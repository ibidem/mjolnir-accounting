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

	/** @var array cache */
	protected static $signs = [];

	/**
	 * @return string table
	 */
	static function typetable()
	{
		return \app\AcctgTAccountTypeLib::table();
	}

	/**
	 * The absolute formula sign is generated on the basis of the right side of
	 * the accounting equation being moved to the left and the entire formula
	 * equaling 0. ie. A - L - OE + E - R = 0
	 *
	 * @return int +1 or -1
	 */
	static function absolute_sign($taccount_id, $absolute_formula_sign = false)
	{
		$taccount = static::entry($taccount_id);
		$type_id = $taccount['type'];

		// sign from parents and self
		$signature_trail = static::stash
			(
				__METHOD__,
				'
					SELECT t.sign as sig
					  FROM `'.static::table().'` t

					  JOIN `'.static::table().'` taccount
					    ON taccount.id = :taccount

				     WHERE t.lft <= taccount.lft
					   AND t.rgt >= taccount.rgt
				'
			)
			->key(__FUNCTION__.'__'.$taccount_id)
			->num(':taccount', $taccount_id)
			->run()
			->fetch_all();

		$taccount_sign = \app\Arr::intmul($signature_trail, 'sig');

		// sign from type
		$type_signature_trail = static::stash
			(
				__METHOD__,
				'
					SELECT t.sign as sig
					  FROM `'.static::typetable().'` t

					  JOIN `'.static::typetable().'` type
					    ON type.id = :type_id

				     WHERE t.lft <= type.lft
					   AND t.rgt >= type.rgt;
				'
			)
			->key(__FUNCTION__.'__'.$type_id)
			->num(':type_id', $type_id)
			->run()
			->fetch_all();

		$type_sign = \app\Arr::intmul($type_signature_trail, 'sig');

		if ($absolute_formula_sign)
		{
			$type = static::entry($type_id);

			// get assets root
			$assets_root = \app\AcctgTAccountTypeLib::find_entry(['slugid' => 'assets']);

			if ($type['lft'] >= $assets_root['lft'] && $type['rgt'] <= $assets_root['rgt'])
			{
				$formula_sign = +1;
			}
			else # liabilities account
			{
				$formula_sign = -1;
			}
		}
		else # ignore formula sign
		{
			$formula_sign = +1;
		}

		return $taccount_sign * $type_sign * $formula_sign;
	}

	/**
	 * @return int +1/-1
	 */
	static function sign($taccount)
	{
		if (isset(static::$signs[$taccount]))
		{
			return static::$signs[$taccount];
		}

		$signature_trail = static::stash
			(
				__METHOD__,
				'
					SELECT t.sign as sig
					  FROM `'.static::table().'` t

					  JOIN `'.static::table().'` taccount
					    ON taccount.id = :taccount

				     WHERE t.lft <= taccount.lft
					   AND t.rgt >= taccount.rgt;
				'
			)
			->key(__FUNCTION__.'__'.$taccount)
			->num(':taccount', $taccount)
			->run()
			->fetch_all();

		return static::$signs[$taccount] = \app\Arr::intmul($signature_trail, 'sig');
	}

	/**
	 * Given a taccount -> value association will return the sum, tacking into
	 * account the sign of the account
	 */
	static function sum(array $taccount_values)
	{
		$sum = 0;
		foreach ($taccount_values as $taccount => $value)
		{
			$sum += \intval($value * 100) * static::sign($taccount);
		}

		return $sum;
	}

	// ------------------------------------------------------------------------
	// Factory interface

	/**
	 * Prevent accidental code calls.
	 */
	static function process(array $input)
	{
		throw new \app\Exception('Hardcoded inserts not allowed. Please use tree_push.');
	}

	/**
	 * Prevent accidental code calls.
	 */
	static function update_process($id, array $input)
	{
		throw new \app\Exception('Hardcoded updates not allowed. Please use tree_update.');
	}

	/**
	 * Ensure fields, except lft, rtg
	 */
	static function ensure_fields($id, &$input)
	{
		// Ensure current fields are part of input
		// ---------------------------------------

		$entry = static::entry($id);

		unset($entry[static::tree_lft()]);
		unset($entry[static::tree_rgt()]);

		$input = \app\Arr::merge($entry, $input);

		// Ensure parent key is present
		// ----------------------------

		$prt = static::tree_parentkey();
		isset($input[$prt]) or $input[$prt] = null;
		isset($input['slugid']) or $inpit['slugid'] = null;
	}

	/**
	 * @return \mjolnir\types\Validator
	 */
	static function tree_check(array $input, $context = null)
	{
		$validator = static::check($input);
		static::tree_checks($validator, $input, $context);

		// Ensure parent is of the same type
		// ---------------------------------

		$prt = static::tree_parentkey();
		if ($input[$prt] !== null)
		{
			$parent = static::entry($input[$prt]);

			if ($parent !== null)
			{
				$validator->rule($prt, 'valid-parent-type', $parent['type'] === $input['type']);
			}
		}

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
	static function tree_push(array $input)
	{
		static::cleanup($input);

		// check for errors
		$errors = static::tree_check($input)->errors();

		if (empty($errors))
		{
			\app\SQL::begin();
			try
			{
				static::tree_process($input);
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

	/**
	 * ...
	 */
	static function tree_update_process($id, array $input)
	{
		$fieldlist = static::fieldlist();
		static::tree_updater
			(
				$id,
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
	static function tree_update($id, array $input)
	{
		static::ensure_fields($id, $input);
		static::cleanup($input);

		// check for errors
		$errors = static::tree_check($input, $id)->errors();

		if (empty($errors))
		{
			\app\SQL::begin();
			try
			{
				static::tree_update_process($id, $input);
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

	/**
	 * Cleanup move input.
	 */
	static function move_cleanup(array &$input)
	{
		! empty($input['new_parent']) or $input['new_parent'] = null;
	}

	/**
	 * @return \mjolnir\types\Validator
	 */
	static function move_check($input)
	{
		$error_messages = \app\CFS::config('mjolnir/models/acctg-taccount')['errors'];

		$taccount_exists = static::exists($input['taccount'], 'id');
		$taccount_parent_exists = static::exists($input['new_parent'], 'id');

		$validator = \app\Validator::instance($input)
			->adderrormessages($error_messages)
			->rule('taccount', 'valid', $taccount_exists)
			->rule('new_parent', 'valid', $input['new_parent'] === null || $taccount_parent_exists);

		if ($taccount_exists && $taccount_parent_exists)
		{
			$validator
				->rule
					(
						'new_parent',
						'compatible-taccount-type',
						static::compatible_taccount_type($input['taccount'], $input['new_parent'])
					)
				->rule
					(
						'new_parent',
						'not-recursive',
						// we are testing that the parent is NOT a child, so the bellow parameter order is correct
						! static::tree_node_is_child_of_parent($input['new_parent'], $input['taccount'])
					)
				;
		}

		return $validator;
	}

	/**
	 * @return boolean
	 */
	protected static function compatible_taccount_type($src, $dest)
	{
		$taccount = static::entry($src);
		$parent = static::entry($dest);

		return $taccount['type'] == $parent['type'];
	}

	/**
	 * Moves taccount to another position
	 */
	static function tree_move($input)
	{
		static::move_cleanup($input);

		// check for errors
		$errors = static::move_check($input)->errors();

		if (empty($errors))
		{
			\app\SQL::begin();
			try
			{
				static::tree_move_process($input['taccount'], $input['new_parent']);
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

	// ------------------------------------------------------------------------
	// Helpers

	/**
	 * @return int 0 for no error, or variation
	 */
	static function check_integrity($group = null)
	{
		$taccounts = static::entries(null, null, 0, null, ['group' => $group]);

		$check_sum = 0;
		foreach ($taccounts as &$taccount)
		{
			$taccount['direction'] = static::absolute_sign($taccount['id'], true);
			$taccount['balance'] = static::balance_for($taccount['id']);

			$check_sum += \intval($taccount['balance'] * 100) * $taccount['direction'];
		}

		return $check_sum / 100;
	}

	/**
	 * Given an accounting group, returns the map of slugid -> id for all
	 * taccounts which have a slug assigned.
	 *
	 * @return array
	 */
	static function namedacctsmap($group = null)
	{
		$accts = static::entries(null, null, 0, null, [ 'group' => $group, 'slugid' => ['not' => null] ]);
		return \app\Arr::gatherkeys($accts, 'slugid', 'id');
	}

	/**
	 * @return float
	 */
	static function balance_for($taccount_id)
	{
		return static::statement
			(
				__METHOD__,
				'
					SELECT SUM(op.amount_value * op.type)
					  FROM `'.\app\AcctgTransactionOperationLib::table().'` op

					  JOIN :table entry
						ON op.taccount = entry.id

					 WHERE entry.id = :taccount_id
				'
			)
			->num(':taccount_id', $taccount_id)
			->run()
			->fetch_calc(0.00);
	}

	// ------------------------------------------------------------------------
	// Setup Helpers

	/**
	 * "Primitive" TAccount install.
	 */
	static function install_taccounts($group, $taccounts)
	{
		$typemap = \app\AcctgTAccountTypeLib::typemap();

		foreach ($taccounts as $type => $typetaccounts)
		{
			foreach ($typetaccounts as $key => $taccount)
			{
				if (\is_array($taccount))
				{
					static::setup_add_taccount($typemap[$type], $key, null, $taccount, $group);
				}
				else # no sub accounts
				{
					static::setup_add_taccount($typemap[$type], $taccount, null, null, $group);
				}
			}
		}

		static::install_special_taccounts($group);
	}

	/**
	 * ...
	 */
	static function install_special_taccounts($group)
	{
		\app\AcctgTAccountLib::tree_push
			(
				[
					'type' => \app\AcctgTAccountTypeLib::typebyname('revenue'),
					'title' => 'General Revenue',
					'sign' => +1,
					'parent' => null,
					'group' => $group,
					'slugid' => 'revenue',
				]
			);

		$revenue_taccount = \app\AcctgTAccountLib::last_inserted_id();

		\app\AcctgSettingsLib::push
			(
				[
					'group' => $group,
					'taccount' => $revenue_taccount,
					'slugid' => 'invoice:revenue.acct'
				]
			);

		\app\AcctgTAccountLockLib::push
			(
				[
					'taccount' => $revenue_taccount,
					'issuer' => \app\AcctgSettingsLib::taccountlock_issuer(),
					'cause' => \app\AcctgSettingsLib::taccountlock_cause(),
				]
			);

		\app\AcctgTAccountLib::tree_push
			(
				[
					'type' => \app\AcctgTAccountTypeLib::typebyname('current-assets'),
					'title' => 'Accounts Recievables',
					'sign' => +1,
					'parent' => null,
					'group' => $group,
					'slugid' => 'accts-recievables',
				]
			);

		$recievables_taccount = \app\AcctgTAccountLib::last_inserted_id();

		\app\AcctgSettingsLib::push
			(
				[
					'group' => $group,
					'taccount' => $recievables_taccount,
					'slugid' => 'invoice:recievables.acct'
				]
			);

		\app\AcctgTAccountLockLib::push
			(
				[
					'taccount' => $revenue_taccount,
					'issuer' => \app\AcctgSettingsLib::taccountlock_issuer(),
					'cause' => \app\AcctgSettingsLib::taccountlock_cause(),
				]
			);
	}

	/**
	 * ...
	 */
	protected static function setup_add_taccount($type, $title, $parent = null, $subaccounts = null, $group = null)
	{
		$input = array
			(
				'title' => $title,
				'sign' => +1,
				'type' => $type,
				'parent' => $parent,
				'group' => $group
			);

		\app\AcctgTAccountLib::tree_push($input);
		$id = \app\AcctgTAccountLib::last_inserted_id();

		if ( ! empty($subaccounts))
		{
			foreach ($subaccounts as $key => $taccount)
			{
				if (\is_array($taccount))
				{
					static::setup_add_taccount($type, $key, $id, $taccount);
				}
				else # no sub accounts
				{
					static::setup_add_taccount($type, $taccount, $id, null);
				}
			}
		}
	}

} # class
