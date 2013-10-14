<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgTAccountTypeLib
{
	use \app\Trait_MarionetteLib;
	use \app\Trait_NestedSetModel;

	// ------------------------------------------------------------------------
	// Factory Interface

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

	// ------------------------------------------------------------------------
	// Collection

	/**
	 * @return array
	 */
	static function entries($page = null, $limit = null, $offset = 0, $depth = null, array $constraints = null)
	{
		$lft = static::tree_lft();
		$rgt = static::tree_rgt();

		! empty($depthkey) or $depthkey = 'depth';

		if ($depth != null)
		{
			$constraints[$depthkey] = [ '<=' => $depth ];
		}

		$order = ['entry.lft' => 'asc'];
		$ordersql = \app\SQL::parseorder($order);
		$ORDER_BY = empty($ordersql) ? null : 'ORDER BY '.$ordersql;

		$wheresql = \app\SQL::parseconstraints($constraints);
		$WHERE = empty($wheresql) ? null : 'WHERE '.$wheresql;

		return static::statement
			(
				__METHOD__,
				"
					#!info rgt -> $rgt, lft -> $lft

					SELECT entry.*,
					       (
								SELECT count(*)
								  FROM `".\app\AcctgTAccountLib::table()."` taccount

								 WHERE taccount.type
								    IN (
										SELECT enum.id
										  FROM `".\app\AcctgTAccountTypeLib::table()."` enum
										 WHERE enum.lft >= entry.lft
										   AND enum.rgt <= entry.rgt
									)
						   ) taccountcount

					FROM
					(

						SELECT node.*, (COUNT(parent.id) - 1) $depthkey

						  FROM :table node,
							   :table parent

						 WHERE node.$lft BETWEEN parent.$lft AND parent.$rgt

						 GROUP BY node.id
						 ORDER BY node.$lft

					) entry

					$WHERE
					$ORDER_BY
					LIMIT :limit OFFSET :offset
				"
			)
			->page($page, $limit, $offset)
			->run()
			->fetch_all();

	}

	/**
	 * @return array
	 */
	static function typemap()
	{
		return \app\Arr::gatherkeys(static::entries(), 'slugid', 'id');
	}

	// ------------------------------------------------------------------------
	// Helpers

	/**
	 * @return string slugid type corresponding to taccount id
	 */
	static function slugid_for_acct($taccount_id)
	{
		return static::entry(\app\AcctgTAccountLib::entry($taccount_id)['type'])['slugid'];
	}

	/**
	 * @todo refactor type system to accept nested types
	 *
	 * @return boolean true if taccount type is compatible with expected type
	 */
	static function matchcheck($taccount_id, $expected_type)
	{
		$taccount_type = static::slugid_for_acct($taccount_id);
		return $taccount_type == $expected_type;
	}

	/**
	 * ...
	 */
	static function install(\mjolnir\types\SQLDatabase $db)
	{
		\app\SQL::session($db);

		// inject taccount types
		$raw_taccount_types = \app\Arr::trim(\app\CFS::config('timeline/mjolnir-accounting/1.0.0/taccount-types'));
		$taccount_types = \app\Arr::hierarchy_from($raw_taccount_types);

		$refs = [];
		$fields = static::fieldlist();
		foreach ($taccount_types as $key => $typeinfo)
		{
			static::tree_inserter($typeinfo, $fields['strs'], $fields['bools'], $fields['nums']);
			$refs[$key] = static::last_inserted_id();
			static::recursive_install($typeinfo['subentries'], $refs, $fields);
		}

		\app\SQL::endsession();
	}

	/**
	 * ...
	 */
	protected static function recursive_install($entries, $refs, $fields)
	{
		foreach ($entries as $key => $typeinfo)
		{
			$typeinfo['parent'] = $refs[$typeinfo['parent']];
			static::tree_inserter($typeinfo, $fields['strs'], $fields['bools'], $fields['nums']);
			$refs[$key] = static::last_inserted_id();
			static::recursive_install($typeinfo['subentries'], $refs, $fields);
		}
	}

} # class
