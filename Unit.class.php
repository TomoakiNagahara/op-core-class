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
	use OP_UNIT_MAPPER;

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

	/**	Return always new instance.
	 *
	 * @param  string $name
	 * @return object
	 */
	static function Instantiate(string $name) : IF_UNIT
	{
		//	Automatically load unit.
		if(!self::Load($name)){
			throw new \Exception("This unit could not be loaded: $name");
		}

		//	Generate name space path.
		$class = '\OP\UNIT\\'.$name;

		//	Check if class include.
		if(!class_exists($class, true) ){
			throw new \Exception("This class could not be the included: $class");
		}

		//	Return new instance.
		return new $class();
	}

	/**	Return already instantiated instance that unit name.
	 *
	 * <pre>
	 * OP()->Unit()->Instantiated('unit_name');
	 * </pre>
	 *
	 * @created  2019-09-18
	 * @renamed  2024-03-20  Singleton() --> Instantiated()
	 * @param    string      $name
	 * @return  &IF_UNIT     $unit
	 */
	static function & Instantiated(string $name) : IF_UNIT
	{
		//	...
		static $_unit;

		//	...
		if( empty($_unit[$name]) ){
			$_unit[$name] = self::Instantiate($name);
		}

		//	...
		return $_unit[$name];
	}
}
