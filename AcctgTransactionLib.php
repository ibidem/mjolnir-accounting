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

	/**
	 * @return string date of first transaction
	 */
	static function startoftime()
	{
		$result = static::statement
			(
				__METHOD__,
				'
					SELECT MIN(date)
					  FROM :table
				'
			)
			->run()
			->fetch_calc();

		if ($result !== null)
		{
			return $result;
		}
		else # result === null
		{
			return \date('Y-m-d');
		}
	}

} # class
