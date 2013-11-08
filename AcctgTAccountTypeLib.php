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

	/**
	 * Roots are always considered positive as per their definition.
	 *
	 * @return int +1/-1
	 */
	static function sign($taccounttype)
	{
		$signature_trail = static::statement
			(
				__METHOD__,
				'
					SELECT entry.sign as sig
					  FROM `'.static::table().'` entry

					  JOIN `'.static::table().'` type
					    ON type.id = :taccounttype

				     WHERE entry.lft <= type.lft
					   AND entry.rgt >= type.rgt;
				'
			)
			->num(':taccounttype', $taccounttype)
			->run()
			->fetch_all();

		return \app\Arr::intmul($signature_trail, 'sig');
	}

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
		$prt = static::tree_parentkey();

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
					#!info rgt -> $rgt, lft -> $lft, prt -> $prt

					SELECT entry.*,
					       (
					           SELECT mirror.id
					             FROM :table mirror
					            WHERE mirror.lft < entry.lft
				                  AND mirror.rgt > entry.rgt
				                ORDER BY mirror.rgt - entry.rgt ASC
				                LIMIT 1
							) AS $prt,
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

	/**
	 * @return array
	 */
	static function inferred_types_slugarray(array $types)
	{
		$inferred = [];
		foreach ($types as $type)
		{
			$type_id = static::find_entry(['slugid' => $type])['id'];
			$inferred = \app\Arr::merge($inferred, static::inferred_types($type_id));
		}

		return \array_unique($inferred);
	}

	/**
	 * @return array
	 */
	static function inferred_types($type)
	{
		$tabledata = static::statement
			(
				__METHOD__,
				'
					SELECT entry.id
					 FROM :table entry

					 JOIN `'.static::table().'` ref
					   ON ref.id = :target

					 WHERE entry.lft >= ref.lft
					   AND entry.rgt <= ref.rgt
				'
			)
			->num(':target', $type)
			->run()
			->fetch_all();

		return \app\Arr::gather($tabledata, 'id');
	}

	/**
	 * @return array
	 */
	static function inferred_types_by_name($type_slugid)
	{
		$entry = static::find_entry(['slugid' => $type_slugid]);

		if ($entry === null)
		{
			throw new \app\Exception('Type of slugid ['.$type_slugid.'].');
		}
		else # $entry !== null
		{
			return static::inferred_types($entry['id']);
		}
	}

	/**
	 * @return id type id
	 */
	static function typebyname($typename)
	{
		$entry = \app\AcctgTAccountTypeLib::find_entry(['slugid' => $typename]);

		if ($entry === null)
		{
			throw new \app\Exception('The system does not know of any type called ['.$typename.'].');
		}
		else # entry !== null
		{
			return $entry['id'];
		}
	}

	/**
	 * @return int
	 */
	static function typefortaccount($taccount)
	{
		return \app\AcctgTAccountLib::find_entry(['id' => $taccount])['type'];
	}

	/**
	 * @return array type slugids for a given taccount
	 */
	static function alltypesfortaccount($taccount)
	{
		$entries = static::statement
			(
				__METHOD__,
				'
					SELECT entry.slugid id
					  FROM :table entry

					  JOIN `'.\app\AcctgTAccountTypeLib::table().'` target
						ON target.id = :target

					 WHERE entry.lft <= target.lft
					   AND entry.rgt >= target.rgt
				'
			)
			->num(':target', static::typefortaccount($taccount))
			->run()
			->fetch_all();

		return \app\Arr::gather($entries, 'id');
	}

} # class
