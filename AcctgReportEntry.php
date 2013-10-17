<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgReportEntry extends \app\Instantiatable implements AcctgReportEntryInterface
{
	use \app\Trait_AcctgReportEntry;

	/**
	 * ...
	 */
	static function maxcols()
	{
		throw new \app\Exception_NotImplemented();
	}

} # class
