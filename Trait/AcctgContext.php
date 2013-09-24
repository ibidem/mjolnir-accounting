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
	function acctgtypes
		(
			$page = null, $limit = null, $offset = 0,
			array $order = null,
			array $constraints = null
		)
	{
		return \app\AcctgTAccountTypeLib::entries
			($page, $limit, $offset, $order, $constraints);
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
	 * This method is designed to be used in conjuction with
	 * HTMLFormField_Select objects,
	 *
	 * eg.
	 *
	 *	$form->select('Example Select', 'example)
	 *		->options_liefhierarchy($context->acctgtaccounts_options_liefhierarchy())
	 *
	 * @return array taccounts as liefhierarchy array
	 */
	function acctgtaccounts_options_liefhierarchy(array $constraints = null, $indenter = null, $accountslabel = null, $blanklabel = null, $blankkey = null)
	{
		$indenter !== null or $indenter = ' &mdash; ';
		$accountslabel !== null or $accountslabel = \app\Lang::term('Accounts');
		$blanklabel !== null or $blanklabel = '- '.\app\Lang::term('no parent').' -';
		$blankkey !== null or $blankkey = '';

		$options = array
			(
				$blankkey => $blanklabel,
				$accountslabel => [],
			);

		$depthgroups = [ 0 => &$options[$accountslabel] ];

		$taccounts = static::acctgtaccounts(null, null, 0, null, $constraints);

		foreach ($taccounts as $taccount)
		{
			if ($taccount['rgt'] - $taccount['lft'] == 1)
			{
				$depthgroups[$taccount['depth']][$taccount['id']] = \str_repeat($indenter, $taccount['depth'] + 1).$taccount['title'];
			}
			else # rgt - lft > 1, node has children
			{
				$key = \str_repeat($indenter, $taccount['depth'] + 1).$taccount['title'];
				$depthgroups[$taccount['depth']][$key] = [];
				$depthgroups[$taccount['depth'] + 1] = &$depthgroups[$taccount['depth']][$key];
			}
		}

		return $options;
	}

	/**
	 * This method is designed to be used in conjuction with
	 * HTMLFormField_Select objects.
	 *
	 * Unlike the liefhierarchy equivalent using this method will make all
	 * accounts selectable. The method is used in cases such as assigning a
	 * parent taccount on creation.
	 *
	 * eg.
	 *
	 *	$form->select('Example Select', 'example)
	 *		->options_logical($context->acctgtaccounts_options())
	 *
	 * @return array taccounts as hierarchy array
	 */
	function acctgtaccounts_options_hierarchy(array $constraints = null, $indenter = null, $accountslabel = null, $blanklabel = null, $blankkey = null)
	{
		$indenter !== null or $indenter = ' &mdash; ';
		$accountslabel !== null or $accountslabel = \app\Lang::term('Accounts');
		$blanklabel !== null or $blanklabel = '- '.\app\Lang::term('no parent').' -';
		$blankkey !== null or $blankkey = '';

		$options = array
			(
				$blankkey => $blanklabel,
				$accountslabel => null,
			);

		$taccounts = static::acctgtaccounts(null, null, 0, null, $constraints);

		foreach ($taccounts as $taccount)
		{
			$options[$taccount['id']] = \str_repeat($indenter, $taccount['depth'] + 1).$taccount['title'];
		}

		return $options;
	}

	/**
	 * @return array taccounts
	 */
	function acctgtaccounts
		(
			$page = null, $limit = null, $offset = 0, $depth = null,
			array $constraints = null
		)
	{
		$entries = \app\AcctgTAccountLib::tree_entries
			(
				$page, $limit, $offset, $depth,
				$constraints,
				'depth' # key in which to show the depth of the entry
			);

		$this->embed_action_handlers($entries);

		return $entries;
	}

	/**
	 * @return array taccount true hierarchy (ie. arrays in arrays)
	 */
	function acctgtaccounts_hierarchy
		(
			$page = null, $limit = null, $offset = 0, $depth = null,
			array $constraints = null
		)
	{
		$entries = \app\AcctgTAccountLib::tree_hierarchy
			(
				$page, $limit, $offset, $depth,
				$constraints,
				'subtaccounts' # keys for where to store subentries
			);

		$this->embed_action_handlers($entries);

		return $entries;
	}

	/**
	 * @return array
	 */
	function acctgtaccounts_leafs
		(
			$page = null, $limit = null, $offset = 0,
			array $constraints = null
		)
	{
		$constraints !== null or $constraints = [];
		$constraints = \app\Arr::merge($constraints, ['entry.lft' => [ '=' => 'entry.rgt - 1']]);
		return $this->acctgtaccounts($page, $limit, $offset, null, $constraints);
	}

	/**
	 * @return array
	 */
	function acctgtypesmap()
	{
		$types = \app\AcctgTAccountTypeLib::entries(null, null);
		return \app\Arr::tablemap($types, 'id');
	}

	// ------------------------------------------------------------------------
	// Entry Actions

	/**
	 * ...
	 */
	protected function embed_action_handlers(array &$entries)
	{
		#
		# At any point you can invoke $entry['action']('an_action') to generate
		# an apropriate form. Typically used in tables for actions on items.
		#

		$context = &$this;
		foreach ($entries as & $entry)
		{
			$entry['action'] = function ($action) use ($entry, $context)
				{
					return $context->acctg_taccount_action($entry, $action);
				};
		}
	}

	/**
	 * General purpose action handler. Overwrite if you need to integrate
	 * special parameters into the action or change the route.
	 *
	 * @return string action url
	 */
	protected function acctg_taccount_action($entry, $action)
	{
		return \app\URL::href
			(
				'taccount.public',
				[
					'action' => $action,
					'id' => $entry['id']
				]
			);
	}

} # trait
