<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   AcctProcedure
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgProcedure_Check extends \app\AcctgProcedure
{
	/** @var string */
	protected static $driverkey = 'check';

	/**
	 * @return array
	 */
	function options_taccounts()
	{
		return $this->context->acctgtaccounts_options_liefhierarchy
			(
				['entry.type' => $this->context->acctgtype('bank')]
			);
	}

	/**
	 * @return array
	 */
	function options_orderof()
	{
		return []; # @todo
	}

} # class
