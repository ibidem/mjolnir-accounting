<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgSettingsLib
{
	use \app\Trait_ModelLib;

	/** @var string table */
	static $table = 'acctg__settings';

	protected static $fields = array
		(
			'nums' => array
				(
					'id',
					'group',
					'taccount'
				),
			'strs' => array
				(
					'slugid'
				),
			'bools' => array
				(
					// empty
				)
		);

	// ------------------------------------------------------------------------
	// Factory interface

	/**
	 * ...
	 */
	static function cleanup(&$input)
	{
		// empty
	}

	/**
	 * @return \mjolnir\types\Validator
	 */
	static function check($input, $context = null)
	{
		// @todo proper validation
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

	/**
	 * ...
	 */
	static function update_process($id, $input)
	{
		// ensure existing values
		$input = \app\Arr::merge(static::entry($id), $input);

		// update entry
		$fields = static::fieldlist();
		static::updater($id, $input, $fields['strs'], $fields['bools'], $fields['nums'])->run();
		static::clear_cache();
	}

	// ------------------------------------------------------------------------
	// etc

	/**
	 * @return string
	 */
	static function taccountlock_issuer()
	{
		return 'mjolnir:acctg-settings';
	}

	/**
	 * @return string
	 */
	static function taccountlock_cause()
	{
		return 'accounting system settings';
	}

} # class
