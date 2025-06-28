<?php
/**	op-core-class:/DebugBacktrace.class.php
 *
 * @created    2023-11-06
 * @version    1.0
 * @package    op-core
 * @subpackage class
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All right reserved.
 */

/**	namespace
 *
 */
namespace OP;

/**	Display debug_backtrace() variable.
 *
 * @created    2023-11-06
 * @version    1.0
 * @author     Tomoaki Nagahara
 * @copyright  Tomoaki Nagahara All right reserved.
 */
class DebugBacktrace
{
	/**	use
	 *
	 */
	use OP_CORE, OP_CI;

	/**	Padding of file path.
	 *
	 * @var integer
	 */
	static private $_file_path_padding = 20;

	/**	Prepare file path padding length.
	 *
	 * @created    2024-03-14
	 * @param     &array      $backtraces
	 */
	static private function _file_path_padding_prepare(array &$backtraces)
	{
		//	...
		foreach( $backtraces as $backtrace ){
			//	...
			if(!$file = $backtrace['file'] ?? null ){
				continue;
			}

			//	...
			self::_file_path_padding($file);
		}
	}

	/**	Save file path padding length.
	 *
	 * @created    2024-03-14
	 * @param      string     $full_path
	 * @return     string     $meta_path
	 */
	static private function _file_path_padding(string $file)
	{
		//	...
		if( $file ){
			$file = OP::Path($file);
		}

		//	...
		$file = str_pad($file, self::$_file_path_padding, ' ', STR_PAD_RIGHT);
		if( self::$_file_path_padding < $len = strlen($file) ){
			self::$_file_path_padding = $len;
		}

		//	...
		return $file;
	}

	/**	Automatically display.
	 *
	 * @param   array   $backtrace
	 */
	static function Auto( $backtrace=null )
	{
		//  ...
		if( empty($backtrace) ){
			$backtrace = debug_backtrace();
		}

		//	...
		self::_file_path_padding_prepare($backtrace);

		//  ...
		foreach( $backtrace as $numerator ){
			echo self::Numerator($numerator);
		}
	}

	/**	Numerator
	 *
	 * @param   array   $numerator
	 * @return  string
	 */
	static function Numerator( array $numerator ) : string
	{
		/*
		//	...
		if( self::$_is_admin === null ){
			self::_is_admin();
		}

		//	...
		if(!self::$_is_admin ){
			return 'Your not is admin.';
		}
		*/

		//	...
		if(!OP::isAdmin() ){
			return '';
		}


		//  ...
		$file     = $numerator['file']     ?? '';
		$line     = $numerator['line']     ?? '';
		$class    = $numerator['class']    ?? '';
		$type     = $numerator['type']     ?? '';
		$function = $numerator['function'] ?? '';
		$args     = $numerator['args']     ?? [];

		/*
		//  ...
		if( $file ){
			if( $temp = CompressPath($file) ){
				$file = $temp;
			}
		}
		$file = str_pad($file, self::$_file_path_padding, ' ', STR_PAD_RIGHT);
		if( self::$_file_path_padding < $len = strlen($file) ){
			self::$_file_path_padding = $len;
		}
		*/
		$file = self::_file_path_padding($file);

		//  ...
		$line = (string)$line;
		$line = str_pad($line,  3, ' ', STR_PAD_LEFT );

		//  ...
		$args = self::Args($args);

		//  ...
		if( $function ){
			$function .= "({$args})";
		}

		//	...
		if( $type ){
			$bulk = $class.$type.$function;
		}else{
			$bulk = $function;
		}

		//  ...
		return "{$file} {$line} - {$bulk}\n";
	}

	/**	Converts the argument array to a string and returns it.
	 *
	 * @param   array   $args
	 * @return  string
	 */
	static function Args(array $args=[]) : string
	{
		//  ...
		$join = [];

		//  ...
		foreach( $args as $arg){
			$join[] = self::Arg($arg);
		}

		//  ...
		return join(',', $join);
	}

	/**	Convert variable to string.
	 *
	 * @param   mixed
	 * @return  string
	 */
	static function Arg($arg) : string
	{
		//  ...
		switch( $type = gettype($arg) ){
			case 'NULL':
				$arg = 'null';
				break;

			case 'boolean':
				$arg = $arg ? 'true':'false';
				break;

			case 'integer':
			case 'double':
				$arg = (string)$arg;
				break;

			case 'string':
				$arg = str_replace(["\r","\n","\t"],['\r','\n','\t'], $arg);
				$arg = OP()->Encode($arg);
				$arg = trim($arg, "'");
				$arg = '"'.$arg.'"';
				break;

			case 'object':
				$arg = get_class($arg);
				break;

			case 'array':
				$arg = json_encode($arg);
				$arg = str_replace('\/', '/', $arg);
				break;

			default:
				$arg = "Undefined:{$type}";
		}

		//  ...
		return OP::isShell() ? trim(escapeshellarg($arg), "'") : Encode($arg);
	}
}
