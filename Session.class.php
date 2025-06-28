<?php
/**	op-core-class:/Session.class.php
 *
 * @created    2021-05-15
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All right reserved.
 */

/**	namespace
 *
 */
namespace OP;

/**	Session
 *
 * <pre>
 * //  Save the value in the session.
 * $val = Session::Set('key', 'saved value');
 *
 * //  Gets the value saved in the session.
 * $val = Session::Get('key', 'default value, in case of null');
 * </pre>
 *
 * @created    2021-05-15
 * @version    1.0
 * @package    op-core
 * @subpackage class
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All right reserved.
 */
class Session
{
	/**	trait.
	 *
	 */
	use OP_CORE, OP_SESSION, OP_CI;

	/**	Get session value.
	 *
	 * @created   2021-05-15
	 * @param     string       $key
	 * @param     mixed        $default
	 * @return    mixed
	 */
	static function Get(string $key, $default=null)
	{
		return self::Session()[$key] ?? $default;
	}

	/**	Set session value.
	 *
	 * @created   2021-05-15
	 * @param     string       $key
	 * @param     mixed        $val
	 */
	static function Set(string $key, $val)
	{
		self::Session()[$key] = $val;
	}
}
