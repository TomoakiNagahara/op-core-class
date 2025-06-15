<?php
/**	op-core-class:/Unit.class.php
 *
 * @genesis    ????-??-??  at op-core-5:/$this->Unit()
 * @created    2016-11-28  by op-core-7:/Unit.class.php
 * @porting    2025-06-15  to op-core-class:/Unit.class.php
 * @version    1.0
 * @package    op-core-class
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All right reserved.
 */

/**	namespace
 *
 */
namespace OP;

/**	Unit
 *
 * <pre>
 * //	Get instance.
 * $obj = Unit::Instance('UnitName');
 *
 * //	Load static class.
 * \OP\Unit::Load('unit_name');
 *
 * //	Use static class.
 * $val = \OP\UNIT\NAME::Get();
 * </pre>
 *
 * @created    2016-11-28
 * @version    1.0
 * @package    op-core
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All right reserved.
 */
class Unit
{
	/**	trait
	 *
	 */
	use OP_CORE, OP_CI;

	/**	Load of unit controller.
	 *
	 * @created    2016-11-28
	 * @updated    2019-06-13  To simplified.
	 * @param      string      $name
	 * @return     boolean     true is successful.
	 */
	static function Load(string $name) : bool
	{
		//	...
		if( class_exists("\OP\UNIT\{$name}", false) ){
			return true;
		}

		//	...
		$dir  = _ROOT_ASSET_ . 'unit/' . strtolower($name) . '/';
		$path = $dir . 'index.php';

		//	...
		if(!file_exists($dir) ){
			$meta_path = 'git:/asset/unit/' . strtolower($name);
			throw new \Exception("This unit has not been installed: {$meta_path}");
		}

		//	...
		if(!file_exists($path) ){
			throw new \Exception("The `index.php` file does not exists: $path");
		};

		//	...
		return require_once($path);
	}

	/**	Check if that unit is installed.
	 *
	 * @created    2022-11-22
	 * @renamed    2024-03-20  isInstall() --> isInstalled()
	 * @param      string     $name
	 * @return     boolean
	 */
	static function isInstalled(string $name) : bool
	{
		//	Generate target path.
		$path = _ROOT_ASSET_ . '/unit/' . strtolower($name) . '/index.php';

		//	Return result.
		return file_exists($path);
	}
}
