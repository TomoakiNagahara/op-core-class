<?php
/**	op-core-class:/Config.class.php
 *
 * Purpose: Want to combine Env::Get()/Set() and Unit::Config().
 *
 * @created    2019-12-27
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All right reserved.
 */

/**	namespace
 *
 */
namespace OP;

/**	Config
 *
 * @created    2019-12-27
 * @version    1.0
 * @package    op-core
 * @subpackage class
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All right reserved.
 */
class Config
{
	/**	trait
	 *
	 */
	use OP_CORE, OP_CI;

	/**	Config
	 *
	 * @created   2019-12-27
	 * @var       array
	 */
	static $_config;

	/**	Init config from asset:/config/{$name}.php
	 *
	 * @created   2019-12-27
	 * @param     string       $name
	 * @return    string       $name
	 */
	static private function _Init(string $name)
	{
		//	Force lower case.
		$name = strtolower($name);

		//	Check by class name whether config is initialized.
		if(!isset(self::$_config[$name]) ){
			//	Fetch from config file.
			self::_Fetch($name);
		}

		//	...
		return $name;
	}

	/**	Fetch from config file.
	 *
	 * @created   2021-05-12
	 * @param     string       $name
	 */
	static private function _Fetch(string $name)
	{
		//	Static variable.
		static $_asset_root, $_config_dir;

		//	Init static variable.
		if(!$_asset_root ){
			//	Get asset root path.
			$_asset_root = _ROOT_ASSET_;

			//	Generate config directory.
			$_config_dir = "{$_asset_root}config/";
		}

		//	Initialize to avoid an infinite loop.
		self::$_config[$name] = [];

		//	Include closure function.
		$include = function($path){ return include($path); };

		//	Ignore "unit" config. --> Got to infinity loop.
		if( $name !== 'unit' ){

			//	Generate file path.
			$path = $_asset_root . "unit/{$name}/config.php";

			//	Check exists.
			if( file_exists($path) ){
				//	Load the config file that each unit has by default.
				self::$_config[$name] = $include($path);
			}
		}

		//	Get current directory.
		$save_directory = getcwd();

		//	Check if config directory exists.
		if( file_exists($_config_dir) ){

			//	Change config directory.
			chdir($_config_dir);

			//	Correspond to overwrite public config at privete local config.
			//	  --> config.php --> _config.php
			foreach([$name, "_{$name}"] as $file_name){
				//	Check if file exists.
				if( file_exists($path = "{$file_name}.php") ){
					//	Include config.
					$config = $include($path);

					//	Check if an array.
					if( gettype($config) !== 'array' ){
						Error::Set("This file does not return an array. `{$path}`");
						continue;
					}

					/**	About array merge.
					 *
					 *  array_replace_recursive() is all replace.
					 *  array_merge_recursive() is number index is renumbering.
					 *
					 */
					self::$_config[$name] = isset(self::$_config[$name]) ? array_replace_recursive(self::$_config[$name], $config) : $config;

					//	Escape.
					continue;
				}

				//	Check if under score file. --> _config.php
				if( $file_name[0] === '_'  ){
					continue;
				}
			}
		}

		//	Recovery save directory.
		chdir($save_directory);

		//	...
		if( empty(self::$_config[$name]) ){
			//	...
			if( !file_exists( "{$_asset_root}config/{$name}.php") and
				!file_exists( "{$_asset_root}layout/{$name}/config.php") and
				!file_exists( "{$_asset_root}unit/{$name}/config.php") ){
				Error::Set("This config file does not exists. ($name)");
			}
		}
	}

	/**	Get
	 *
	 * @created   2019-12-27
	 */
	static function Get($name)
	{
		//	...
		$name = self::_Init($name);

		//	...
		return self::$_config[$name] ?? [];
	}

	/**	Set
	 *
	 * @created   2019-12-13   Moved Env::Get() --> OP_UNIT::Config()
	 * @moved     2019-12-27   Moved OP_UNIT::Config() --> Config::Set()
	 * @param     string       $name
	 * @param     mixed        $config
	 * @return    mixed
	 */
	static function Set($name, $config)
	{
		//	...
		$name = self::_Init($name);

		/**	About array merge.
		 *
		 *  array_replace_recursive() is all replace.
		 *  array_merge_recursive() is number index is renumbering.
		 */
		//	self::$_env[$key] = array_merge_recursive(self::$_env[$key], $var);
		if( $config ){
			//	...
			if( self::$_config[$name] === null ){
				self::$_config[$name]  =  [];
			}

			//	...
			self::$_config[$name] = array_replace_recursive(self::$_config[$name], $config);
		}

		//	...
		return self::$_config[$name];
	}
}
