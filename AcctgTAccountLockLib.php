<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgTAccountLockLib
{
	use \app\Trait_ModelLib;

	/** @var string */
	protected static $table = 'acctg__taccountlocks';

	/** @var array */
	protected static $fields = array
		(
			'nums' => array
				(
					'id',
					'taccount',
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

	// ------------------------------------------------------------------------
	// Factory interface

	/**
	 * @return \app\Validator
	 */
	static function check($input, $context = null)
	{
		return \app\Validator::instance($input);
	}

	/**
	 * ...
	 */
	static function process($input)
	{
		$fields = static::fieldlist();
		
		static::inserter
			(
				$input,
				$fields['strs'],
				$fields['bools'],
				\array_diff($fields['nums'], ['id'])
			)
			->run();

		static::clear_cache();
	}

} # class
