<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgTAccountLib
{
	use \app\Trait_MarionetteLib;
	use \app\Trait_NestedSetModel;

	// ------------------------------------------------------------------------
	// Factory interface

	/**
	 * ...
	 */
	static function process(array $input)
	{
		$fieldlist = static::fieldlist();
		static::inserter
			(
				$input,
				$fieldlist['strs'], $fieldlist['bools'], $fieldlist['nums']
			)
			->run();

		// cleanup
		static::clear_cache();
	}

	/**
	 * @return mjolnir\types\Validator
	 */
	static function tree_check(array $fields, $context = null) {
		$validator = static::check($fields);
		static::tree_checks($validator, $fields, $context);
		return $validator;
	}

	/**
	 * ...
	 */
	static function tree_process(array $input)
	{
		$fieldlist = static::fieldlist();
		static::tree_inserter
			(
				$input,
				$fieldlist['strs'], $fieldlist['bools'], $fieldlist['nums']
			);

		// cleanup
		static::clear_cache();
	}

	/**
	 * Verifies and creates entry.
	 *
	 * @return array or null
	 */
	static function tree_push(array $fields)
	{
		static::cleanup($fields);

		// check for errors
		$errors = static::tree_check($fields)->errors();

		if (empty($errors))
		{
			\app\SQL::begin();
			try
			{
				static::tree_process($fields);
				\app\SQL::commit();
			}
			catch (\Exception $e)
			{
				\app\SQL::rollback();
				throw $e;
			}

			return null;
		}
		else # got errors
		{
			return $errors;
		}
	}

} # class
