<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgJournalModel extends \app\MarionetteModel
{
	/**
	 * @var array
	 */
	static $configfile = 'mjolnir/models/acctg-journal';

	/**
	 * @return array parsed input
	 */
	function parse(array $input)
	{
		isset($input['group']) or $input['group'] = null;
		return parent::parse($input);
	}

} # class
