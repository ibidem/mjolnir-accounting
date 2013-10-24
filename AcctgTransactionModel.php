<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgTransactionModel extends \app\MarionetteModel
{
	/**
	 * @var string
	 */
	static $configfile = 'mjolnir/models/acctg-transaction';

	/**
	 * @return array parsed input
	 */
	function parse(array $input)
	{
		isset($input['group']) or $input['group'] = null;
		return parent::parse($input);
	}

} # class
