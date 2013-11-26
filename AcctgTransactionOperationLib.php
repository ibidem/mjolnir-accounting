<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgTransactionOperationLib
{
	use \app\Trait_MarionetteLib;

	/**
	 * @return int +1 for Dr and -1 for Cr
	 */
	static function atomictype($operation)
	{
		if ( ! \is_array($operation))
		{
			$operation = static::entry($operation);
		}

		return $operation['type']
			* \app\AcctgTAccountLib::rootsign($operation['taccount'])
			* \app\AcctgTAccountTypeLib::rootsign(\app\AcctgTAccountLib::entry($operation['taccount'])['type']);
	}

} # class
