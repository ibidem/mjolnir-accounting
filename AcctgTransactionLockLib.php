<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgTransactionLockLib
{
	use \app\Trait_ModelLib;

	/** @var string */
	protected static $table = 'acctg__transactionlocks';

	/** @var array */
	protected static $fields = array
		(
			'nums' => array
				(
					'id',
					'transaction',
				),
			'strs' => array
				(
					'issuer',
					'cause'
				),
			'bools' => array
				(
					// empty
				),
		);

} # class
