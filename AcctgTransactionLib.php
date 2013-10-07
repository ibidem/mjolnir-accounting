<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgTransactionLib
{
	use \app\Trait_MarionetteLib;

	/**
	 * @return \mjolnir\types\Validator
	 */
	static function integrity_validator($input, $context = null)
	{
		return \app\Validator::instance($input);
	}

} # class
