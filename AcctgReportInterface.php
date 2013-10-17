<?php namespace mjolnir\accounting;

use \mjolnir\types\Meta as Meta;
use \mjolnir\types\Renderable as Renderable;
use \mjolnir\types\Executable as Executable;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013 Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
interface AcctgReportInterface extends Executable, Renderable, Meta
{
	/**
	 * eg. a BalanceSheet report might return "Balance Sheet"
	 *
	 * This method is useful for dynamic resolution and general templates.
	 *
	 * @return string report title
	 */
	function title();

	/**
	 * The time the report was generated. Some reports may determine that there
	 * is absolutely no reason to rerun and may simply return a cached result
	 * set (ie. the report of a past point in time).
	 *
	 * This method will always return the actual time the report was generated;
	 * not the current time (ie. the time the report was displayed).
	 *
	 * @return int timestamp
	 */
	function timestamp();

	/**
	 * The report's interval as deduced from the options.
	 *
	 * @return string
	 */
	function reportinterval();

	/**
	 * @return \mjolnir\types\Validator
	 */
	function validator();

} # interface
