<?php namespace mjolnir\accounting;

use \mjolnir\types\Renderable as Renderable;
use \mjolnir\types\Meta as Meta;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013 Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
interface AcctgReportEntryInterface extends Renderable, Meta
{
	/**
	 * @return string
	 */
	function render($indent = null);

} # interface
