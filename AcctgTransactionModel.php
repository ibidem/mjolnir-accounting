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

		if (isset($input['date']))
		{
			$input['date'] = \date_create($input['date'])->format('Y-m-d');
		}
		else # no date passed
		{
			$input['date'] = null;
		}

		return parent::parse($input);
	}

} # class
