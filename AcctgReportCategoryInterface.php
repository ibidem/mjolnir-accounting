<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013 Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
interface AcctgReportCategoryInterface extends AcctgReportEntryInterface
{
	/**
	 * @return AcctgReportCategoryInterface
	 */
	function newcategory($title);

} # interface
