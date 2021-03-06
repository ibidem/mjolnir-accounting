<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgTransactionOperationCollection extends \app\MarionetteCollection
{
	/**
	 * @return array
	 */
	static function config()
	{
		return \app\AcctgTransactionOperationModel::config();
	}

} # class
