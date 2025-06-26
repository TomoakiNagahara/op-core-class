<?php
/**	op-core-class:/MetaPath.class.php
 *
 * @created    2022-06-11
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

/**	MetaPath
 *
 * @created    2022-06-11
 * @version    1.0
 * @package    op-core
 * @subpackage class
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All right reserved.
 */
class MetaPath
{
	/**	trait
	 *
	 */
	use OP_CORE, OP_CI;

	/**	Set meta root path.
	 *
	 * <pre>
	 * OP()->MetaPath()->Set('meta_label', __DIR__);
	 * </pre>
	 *
	 * @created   2022-06-11
	 * @param     string     $meta
	 * @param     string     $path
	 */
	static function Set(string $meta, string $path)
	{
		require_once(_ROOT_CORE_.'/function/RootPath.php');
		return RootPath($meta, $path);
	}

	/**	Get path by meta label.
	 *
	 * <pre>
	 * OP()->MetaPath()->Get('meta_label');
	 * </pre>
	 *
	 * @created   2022-06-11
	 * @param     string     $meta
	 * @return    string|boolean
	 */
	static function Get(string $meta)
	{
		require_once(_ROOT_CORE_.'/function/RootPath.php');
		return RootPath($meta);
	}

	/**	Get meta root path list.
	 *
	 * @created   2022-06-11
	 * @return    array
	 */
	static function List() : array
	{
		//	...
		require_once(_ROOT_CORE_.'/function/RootPath.php');
		return RootPath();
	}

	/**	Convert to the meta-path from the full-path.
	 *
	 * @created   2022-06-11
	 * @param     string     $path
	 * @return    string|boolean|null
	 */
	static function Encode(string $path)
	{
		//	...
		require_once(_ROOT_CORE_.'/function/CompressPath.php');
		return CompressPath($path);
	}

	/**	Restore to the full-path from the meta-path.
	 *
	 * @created   2022-06-11
	 * @param     string     $path
	 * @return    string|boolean|null
	 */
	static function Decode(string $path)
	{
		//	...
		require_once(_ROOT_CORE_.'/function/ConvertPath.php');
		return ConvertPath($path, false, false);
	}

	/**	Convert to Document root URL from meta path and full path.
	 *
	 * @created    2022-10-16
	 * @param      string     $path
	 * @return     string     $URL
	 */
	static function URL($path)
	{
		require_once(_ROOT_CORE_.'/function/ConvertURL-2.php');
		return ConvertURL($path);
	}
}
