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
	 * @return array
	 */
	function acctgtypesmap()
	{
		$types = \app\AcctgTAccountTypeLib::entries(null, null);
		return \app\Arr::tablemap($types, 'id');
	}

	/**
	 * @return int type
	 */
	function acctgtype($type)
	{
		$types = \app\AcctgTAccountTypeLib::entries(null, null);
		return \app\Arr::gatherkeys($types, 'slugid', 'id')[$type];
	}

	/**
	 * @return array
	 */
	function acctgtaccount($id)
	{
		$taccount = \app\AcctgTAccountLib::entry($id);
		$this->embed_taccount_handlers($taccount);
		return $taccount;
	}

	// ------------------------------------------------------------------------
	// Internal

	/**
	 * @return array
	 */
	function acctgroutemap()
	{
		return \app\CFS::config('mjolnir/acctg/routes');
	}

	// ------------------------------------------------------------------------
	// Acctg Collections

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
			$this->embed_taccount_handlers($entry);
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
			$this->recusively_embed_taccount_handlers($taccount, $subtreekey);
		}

		return $entries;
	}

	/**
	 * Adds handlers to entry and subaccounts.
	 */
	protected function recusively_embed_taccount_handlers(&$taccount, $subtreekey)
	{
		$this->embed_taccount_handlers($taccount);
		if ( ! empty($taccount[$subtreekey]))
		{
			foreach ($taccount[$subtreekey] as &$subtaccount)
			{
				$this->recusively_embed_taccount_handlers($subtaccount, $subtreekey);
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
		$constraints = \app\Arr::merge($constraints, ['entry.lft' => [ '=' => 'entry.rgt - 1' ]]);
		return $this->acctgtaccounts($page, $limit, $offset, null, $constraints);
	}

	/**
	 * @return array
	 */
	function acctgjournals
		(
			$page = null, $limit = null, $offset = 0,
			array $order = null, array $constraints = null
		)
	{
		$entries = \app\AcctgJournalLib::entries
			(
				$page, $limit, $offset,
				$order, $constraints
			);

		foreach ($entries as &$entry)
		{
			$this->embed_journal_handlers($entry);
		}

		return $entries;
	}

	/**
	 * @return array
	 */
	function acctgtransactions_hierarchical
		(
			$journal_id,
			$page = null, $limit = null, $offset = 0,
			array $order = null, array $constraints = null
		)
	{
		$constraints['journal'] = $journal_id;

		// @todo CLEANUP look into optimization oportunities for this method

		#
		# The following implementation is intentionally crute and simple.
		#

		// Retrieve transactions
		// ---------------------

		$transactions = \app\AcctgTransactionLib::entries
			(
				$page, $limit, $offset,
				$order, $constraints
			);

		// embed handlers
		foreach ($transactions as &$transaction)
		{
			$this->embed_transaction_handlers($transaction);
		}

		// Retrieve operations
		// -------------------

		foreach ($transactions as &$transaction)
		{
			$transaction['operations'] = $this->acctgoperations($transaction['id']);
		}

		// Create year -> month-day -> operations hierarchy
		// ------------------------------------------------

		// this hierarchical structure is meant to emulate pen and paper
		// journals as closely as possible

		$hierarchy = [];

		foreach ($transactions as &$transaction)
		{
			// ensure structure is present
			$date = \date_create($transaction['date']);
			$year = $date->format('Y');
			$month = $date->format('m');
			$day = $date->format('d');
			isset($hierarchy[$year]) or $hierarchy[$year] = [];
			isset($hierarchy[$year][$month]) or $hierarchy[$year][$month] = [];
			isset($hierarchy[$year][$month][$day]) or $hierarchy[$year][$month][$day] = [];

			// save entry under specific date
			$hierarchy[$year][$month][$day][] = $transaction;
		}

		return $hierarchy;
	}

	/**
	 * @return array
	 */
	function acctgoperations
		(
			$transaction_id,
			$page = null, $limit = null, $offset = 0,
			array $order = null, array $constraints = null
		)
	{
		$constraints['transaction'] = $transaction_id;

		$operations = \app\AcctgTransactionOperationLib::entries
			(
				$page, $limit, $offset,
				$order, $constraints
			);

		return $operations;
	}

	// ------------------------------------------------------------------------
	// Form Helpers

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
		$blanklabel !== null or $blanklabel = '- '.\app\Lang::term('select account').' -';
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

	// ------------------------------------------------------------------------
	// Entry Actions

	/**
	 * ...
	 */
	protected function embed_taccount_handlers(array &$taccount)
	{
		$control_context = &$this;

		// At any point you can invoke $entry['action']('an_action') to generate
		// an apropriate form. Typically used in tables for actions on items.
		$taccount['action'] = function ($action) use ($taccount, $control_context)
			{
				return $control_context->acctg_taccount_action($taccount, $action);
			};

		// Similarly you can also call $entry['can']('an_action') for an access
		// check on the action in question.
		$taccount['can'] = function ($action, $context = null, $attributes = null, $user_role = null) use ($taccount, $control_context)
			{
				return $control_context->acctg_taccount_can($taccount, $action, $context = null, $attributes = null, $user_role = null);
			};
	}

	/**
	 * ...
	 */
	protected function embed_journal_handlers(array &$journal)
	{
		$control_context = &$this;

		// At any point you can invoke $entry['action']('an_action') to generate
		// an apropriate form. Typically used in tables for actions on items.
		$journal['action'] = function ($action) use ($journal, $control_context)
			{
				return $control_context->acctg_journal_action($journal, $action);
			};

		// Similarly you can also call $entry['can']('an_action') for an access
		// check on the action in question.
		$journal['can'] = function ($action, $context = null, $attributes = null, $user_role = null) use ($journal, $control_context)
			{
				return $control_context->acctg_journal_can($journal, $action, $context = null, $attributes = null, $user_role = null);
			};
	}

	/**
	 * ...
	 */
	protected function embed_transaction_handlers(array &$transaction)
	{
		$control_context = &$this;

		// At any point you can invoke $entry['action']('an_action') to generate
		// an apropriate form. Typically used in tables for actions on items.
		$transaction['action'] = function ($action) use ($transaction, $control_context)
			{
				return $control_context->acctg_transaction_action($transaction, $action);
			};

		// Similarly you can also call $entry['can']('an_action') for an access
		// check on the action in question.
		$transaction['can'] = function ($action, $context = null, $attributes = null, $user_role = null) use ($transaction, $control_context)
			{
				return $control_context->acctg_transaction_can($transaction, $action, $context = null, $attributes = null, $user_role = null);
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
		$rmap = $this->acctgroutemap();

		return \app\URL::href
			(
				$rmap['acctg-taccount.public'],
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
		$rmap = $this->acctgroutemap();

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
				$rmap['acctg-taccount.public'],
				$action,
				$context,
				$attributes,
				$user_role
			);
	}

	/**
	 * General purpose action handler. Overwrite if you need to integrate
	 * special parameters into the action or change the route.
	 *
	 * @return string action url
	 */
	protected function acctg_journal_action($entry, $action)
	{
		$rmap = $this->acctgroutemap();

		return \app\URL::href
			(
				$rmap['acctg-journal.public'],
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
	protected function acctg_journal_can($entry, $action, $context = null, $attributes = null, $user_role = null)
	{
		$rmap = $this->acctgroutemap();

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
				$rmap['acctg-journal.public'],
				$action,
				$context,
				$attributes,
				$user_role
			);
	}

	/**
	 * General purpose action handler. Overwrite if you need to integrate
	 * special parameters into the action or change the route.
	 *
	 * @return string action url
	 */
	protected function acctg_transaction_action($entry, $action)
	{
		$rmap = $this->acctgroutemap();

		return \app\URL::href
			(
				$rmap['acctg-transaction.public'],
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
	protected function acctg_transaction_can($entry, $action, $context = null, $attributes = null, $user_role = null)
	{
		$rmap = $this->acctgroutemap();

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
				$rmap['acctg-transaction.public'],
				$action,
				$context,
				$attributes,
				$user_role
			);
	}

} # trait
