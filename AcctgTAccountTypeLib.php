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
	use \app\Trait_ModelLib;

	/**
	 * @return string
	 */
	static function table()
	{
		return \app\AcctgTAccountTypeModel::table();
	}

	// ------------------------------------------------------------------------
	// Collection

	/**
	 * @return array
	 */
	static function entries($page = null, $limit = null, $offset = 0, array $order = null, array $constraints = null)
	{
		return static::stash
			(
				__METHOD__,
				'
					SELECT entry.*,
					       (
								SELECT count(*)
								  FROM `'.\app\AcctgTAccountLib::table().'` taccount
								 WHERE taccount.type = entry.id
						   ) taccountcount
					  FROM :table entry
				'
			)
			->page($page, $limit, $offset)
			->key(__METHOD__)
			->order($order)
			->constraints($constraints)
			->run()
			->fetch_all();
	}

} # class
