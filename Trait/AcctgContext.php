<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Trait
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
trait Trait_AcctgContext
{
	/**
	 * @return array taccount types
	 */
	function acctgtypes($page = null, $limit = null, $offset = 0, array $order = null, array $constraints = null)
	{
		return \app\AcctgTAccountTypeLib::entries($page, $limit, $offset, $order, $constraints);
	}

	/**
	 * @return array taccount types as optgroup array
	 */
	function acctgtypes_optgroups(array $constraints = null)
	{
		$types = static::acctgtypes(null, null, 0, null, $constraints);
		$hints = \app\AcctgTAccountTypeHintLib::entries(null, null);
		$hintsmap = \app\Arr::gatherkeys($hints, 'id', 'title');

		$groups = [];
		foreach ($types as $type)
		{
			$group = $hintsmap[$type['typehint']];
			isset($groups[$group]) or $groups[$group] = [];
			$groups[$group][$type['id']] = $type['title'];
		}

		return $groups;
	}

	/**
	 * @return array taccounts
	 */
	function acctgtaccounts($page = null, $limit = null, $offset = 0, array $order = null, array $constraints = null)
	{
		return \app\AcctgTAccountLib::entries($page, $limit, $offset, $order, $constraints);
	}

} # trait
