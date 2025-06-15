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
}
