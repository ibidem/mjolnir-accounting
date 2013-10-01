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

		foreach ($entries as &$entry)
		{
			$this->embed_handlers($entry);
		}

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
		$subtreekey = 'subtaccounts';

		$entries = \app\AcctgTAccountLib::tree_hierarchy
			(
				$page, $limit, $offset, $depth,
				$constraints,
				$subtreekey # keys for where to store subentries
			);

		foreach ($entries as &$taccount)
		{
			$this->recusively_embed_handlers($taccount, $subtreekey);
		}

		return $entries;
	}

	/**
	 * Adds handlers to entry and subaccounts.
	 */
	protected function recusively_embed_handlers(&$taccount, $subtreekey)
	{
		$this->embed_handlers($taccount);
		if ( ! empty($taccount[$subtreekey]))
		{
			foreach ($taccount[$subtreekey] as &$subtaccount)
			{
				$this->recusively_embed_handlers($subtaccount, $subtreekey);
			}
		}
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
	protected function embed_handlers(array &$entry)
	{
		$control_context = &$this;

		// At any point you can invoke $entry['action']('an_action') to generate
		// an apropriate form. Typically used in tables for actions on items.
		$entry['action'] = function ($action) use ($entry, $control_context)
			{
				return $control_context->acctg_taccount_action($entry, $action);
			};

		// Similarly you can also call $entry['can']('an_action') for an access
		// check on the action in question.
		$entry['can'] = function ($action, $context = null, $attributes = null, $user_role = null) use ($entry, $control_context)
			{
				return $control_context->acctg_taccount_action($entry, $action, $context = null, $attributes = null, $user_role = null);
			};
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

	/**
	 * General purpose access control handler. Overwrite if you need to
	 * integrate special parameters into the action or change the route.
	 *
	 * Note: access happens at the domain level so this handler is mostly used
	 * for achieving a consistent visual representation.
	 *
	 * @return string action url
	 */
	protected function acctg_taccount_can($entry, $action, $context = null, $attributes = null, $user_role = null)
	{
		if (\is_string($action))
		{
			$action  = array
				(
					'action' => $action,
					'id' => $entry['id']
				);
		}

		return \app\Access::can
			(
				'taccount.public',
				$action,
				$context,
				$attributes,
				$user_role
			);
	}

} # trait
