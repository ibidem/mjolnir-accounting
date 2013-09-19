<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgJournalLib
{
	use \app\Trait_ModelLib;

	/**
	 * @return string
	 */
	static function table()
	{
		return \app\AcctgJournalModel::table();
	}

} # class
