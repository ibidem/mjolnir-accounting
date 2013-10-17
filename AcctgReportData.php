<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgReportData extends \app\AcctgReportEntry implements AcctgReportDataInterface
{
	use \app\Trait_AcctgReportData;

	/** @var array of \mjolnir\accounting\AcctgReportEntryInterface */
	protected $rows = null;

	/**
	 * Add sub row.
	 */
	static function addentry(AcctgReportDataInterface $row)
	{
		$this->rows[] = $row;
	}

} # class
