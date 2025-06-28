<?php
/**	op-core:/Encrypt.class.php
 *
 * @created    2017-11-22
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All right reserved.
 */

/**	Declare strict
 *
 */
declare(strict_types=1);

/**	namespace
 *
 */
namespace OP;

/**	Encrypt
 *
 * @created    2017-11-22
 * @version    1.0
 * @package    op-core
 * @subpackage class
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All right reserved.
 */
class Encrypt
{
	/**	trait.
	 *
	 * @updated   2022-10-20 OP_CI
	 */
	use OP_CORE, OP_CI;

	/**	Cipher method
	 *
	 * @var string
	 */
	const algorithm = 'aes-256-cbc';

	/**	Generate Initial vector.
	 *
	 */
	static private function _iv()
	{
		$source = $_SERVER["_OP_OPENSSL_IV_"] ?? Env::AppID();
		$source = md5($source);
		return substr($source, 0, 16);
	}

	/**	Generate password.
	 *
	 */
	static private function _password()
	{
		$source = $_SERVER["_OP_OPENSSL_PASSWORD_"] ?? Env::AppID();
		$source = md5($source);
		return $source;
	}

	/**	Enc is Encoding.
	 *
	 * @param string $str
	 * @param string $str
	 */
	static function Enc(string $str)
	{
		//	...
		$iv       = self::_iv();
		$password = self::_password();

		//	...
		return openssl_encrypt($str, self::algorithm, $password, 0, $iv);
	}

	/**	Dec is Decoding.
	 *
	 * @param string $str
	 * @param string $str
	 */
	static function Dec(string $str)
	{
		//	...
		$iv       = self::_iv();
		$password = self::_password();

		//	...
		return openssl_decrypt($str, self::algorithm, $password, 0, $iv);
	}
}
