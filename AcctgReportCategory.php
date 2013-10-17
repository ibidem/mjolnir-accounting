<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgReportCategory
extends \app\AcctgReportEntry
{
	/**
	 * Add subrow
	 */
	static function addnestedcategory(AcctgReportCategoryInterface $row)
	{
		$this->rows[] = $row;
	}

	/**
	 * @return AcctgReportRowInterface
	 */
	function totals()
	{
		throw new \app\Exception_NotImplemented();
	}

	/**
	 * Add subrow
	 */
	static function addentry(AcctgReportEntryInterface $row)
	{
		$this->rows[] = $row;
	}

} # class
