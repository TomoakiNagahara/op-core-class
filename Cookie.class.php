<?php
/**	op-core-class:/Cookie.class.php
 *
 * @created    2017-02-25
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All right reserved.
 */

/**	namespace
 *
 */
namespace OP;

/**	include
 *
 */
include_once(__DIR__.'/../function/Hasha1.php');

/**	Cookie
 *
 * FEATURE:
 * 1. Even the same key name is separated by AppID.
 *    That is, Even in the same domain, the same key name can be used.
 *    Because AppleID is different. Value is do not conflict.
 *
 * 2. Value is encrypted.
 *    Cookie is stored user's browser. That is, User can change freely.
 *
 * @created    2017-02-25
 * @version    1.0
 * @package    op-core
 * @subpackage class
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All right reserved.
 */
class Cookie
{
	/**	trait.
	 *
	 */
	use OP_CORE, OP_CI;

	/**	Get cookie value of key.
	 *
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	static function Get($key, $default=null)
	{
		//	...
		if( OP::isShell() ){
			Error::Set('Cookie can not be used in the shell environment.');
			return;
		}

		/*
		//	...
		if( _OP_APP_BRANCH_ < 2030 ){
		$app_id  = Env::AppID();
		}else{
		$app_id  = _APP_ID_;
		}

		//	Cache feature
		if( isset( $_SESSION[_OP_NAME_SPACE_]['CORE']['COOKIE'][$app_id][$key] ) ){
			return $_SESSION[_OP_NAME_SPACE_]['CORE']['COOKIE'][$app_id][$key];
		}
		*/

		//	...
		$key = Hasha1($key, 16);

		//	...
		return isset($_COOKIE[$key]) ? unserialize( Encrypt::Dec($_COOKIE[$key]) ): $default;
	}

	/**	Set cookie value.
	 *
	 * <pre>
	 * ```php
	 * // Set options.
	 * $option = [
	 *   'path'     => '',
	 *   'domain'   => '',
	 *   'secure'   => '',
	 *   'httponly' => '',
	 *   'samesite' => '', // None, Lax, Strict
	 * ];
	 *
	 * // Set cookie.
	 * OP()->Cookie('count', $count, '+365day', $option);
	 * ```
	 * </pre>
	 *
	 * @param  string         $key
	 * @param  mixed          $val
	 * @param  mixed          $expire 2020-12-31, 86400(60*60*24)
	 * @param  array          $option
	 * @return boolean|string $date
	 */
	static function Set($key, $val, $expire=null, $option=null)
	{
		//	...
		if( OP::isShell() ){
			Error::Set('Cookie can not be used in the shell environment.');
			return;
		}

		/*
		//	...
		if( _OP_APP_BRANCH_ < 2030 ){
		$app_id  = Env::AppID();
		}else{
		$app_id  = _APP_ID_;
		}

		//	Cache feature
		$_SESSION[_OP_NAME_SPACE_]['CORE']['COOKIE'][$app_id][$key] = $val;
		*/

		//	...
		$file = $line = null;

		//	Failed.
		if( headers_sent($file, $line) ){
			Error::Set("Header has already been sent. ($file, $line)");
			return false;
		}

		//	...
		$key = Hasha1($key, 16);

		/**	Separate from ICE AGE time.
		 *  Because expire time is calculate by local browser.
		 */
		$time = time();

		//	null --> current time + 10 year
		if( $expire === null ){
			$expire = $time + (60*60*24*365*10);
		}else
		//	Convert to UTC Unix timestamp from string: 2020-01-01 or +30days --> 1577804400
		if(!is_numeric($expire) ){
			$expire = strtotime($expire);
		}else
		//	60 --> current time + 60 sec
		if( $expire < $time ){
			$expire+= $time;
		}

		//	...
		$val = serialize($val);

		//	...
		$val = Encrypt::Enc($val);

		//	https://www.php.net/manual/ja/function.setcookie.php
		if( version_compare(PHP_VERSION, '7.3.0', '<') ){
			//	Under 7.3.0
			$path     = $option['path']     ?? ConvertURL('app:/');
			//	For CORS
			if( $option['samesite'] ?? null ){
				//	CORS is require "HTTPs".
				$io = setcookie($key, $val, $expire, "{$path}; SameSite=None; Secure");
			}else{
				//	Not CORS
				$domain   = $option['domain']   ?? $_SERVER['SERVER_NAME'];
				$secure   = $option['secure']   ?? false; // If TRUE, Sends the cookie to the server only for https.
				$httponly = $option['httponly'] ?? false; // If TRUE, Cookies can not be referenced from JavaScript.
				//	...
				$io = setcookie($key, $val, $expire, $path, $domain, $secure, $httponly);
			}
		}else{
			//	Upper equal 7.3.0
			//	expires
			if( empty($option['expires']) ){
				$option['expires'] = $expire;
			}
			//	path
			if( empty($option['path']) ){
				$option['path'] = OP()->URL('app:/');
			}
			//	domain, secure, httponly, samesite
			$io = setcookie($key, $val, $option);
		}

		//	...
		if( $io ){
			//	Successful.
			$_COOKIE[$key] = $val;
		}else{
			Error::Set("Set cookie was failed.");
		}

		//	...
		$result = [
			'setcookie' => $io,
			'key'       => $key,
			'value'     => $val,
			'expire'    => [
				'time'  => $expire,
				'date'  => gmdate('Y-m-d H:i:s', $expire),
			],
			'option'    => $option,
		];

		//	...
		return $result;
	}

	/**	User ID
	 *
	 *  This value is please limit to temporary operation.
	 *  Not suitable for permanent use.
	 *
	 * @created   2020-02-26
	 * @param     boolean      $init stored at create the first time UserID.
	 * @return    string       $user_id
	 */
	static function UserID(&$init=null)
	{
		//	...
		if( OP::isShell() ){
			return;
		}

		//	...
		$key = 'UserID';

		//	...
		if(!$user_id = self::Get($key) ){
			$user_id = md5($_SERVER['REMOTE_ADDR'].', '.microtime());

			//	...
			self::Set($key, $user_id);

			//	...
			$init = true;
		}

		//	...
		return $user_id;
	}
}
