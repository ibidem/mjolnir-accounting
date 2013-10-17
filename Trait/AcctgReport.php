<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Trait
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
trait Trait_AcctgReport
{
	use \app\Trait_Executable;
	use \app\Trait_Renderable;
	use \app\Trait_Meta;

	/**
	 * eg. a BalanceSheet report might return "Balance Sheet"
	 *
	 * This method is useful for dynamic resolution and general templates.
	 *
	 * @return string report title
	 */
	function title()
	{
		return $this->get('title', null);
	}

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
	function timestamp()
	{
		return $this->get('timestamp', null);
	}

	/**
	 * The report's interval as deduced from the options.
	 *
	 * @return string
	 */
	function reportinterval()
	{
		return $this->get('reportinterval', null);
	}

	/**
	 * @return \mjolnir\types\Validator
	 */
	protected function default_rules()
	{
		return \app\Validator::instance($this->metadata());
	}

} # trait
