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
			$page = null, $limit = null, $offset = 0, $depth = null,
			array $constraints = null
		)
	{
		return \app\AcctgTAccountTypeLib::entries
			($page, $limit, $offset, $depth = null, $constraints);
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
		$taccounts = static::acctgtaccounts($page, $limit, $offset, $depth, $constraints);
		$types = static::acctgtypes(null, null);
		$tree = \app\Arr::hierarchy_from(\app\Arr::tablemap($types, 'id'));
		$refs = \app\Arr::refs_from($tree, 'id', 'subentries');
		$acctrefs = [];

		foreach ($refs as &$taccounttype)
		{
			$taccounttype['entrytype'] = 'taccount-type';
		}

		$displayed_types = [];
		foreach ($taccounts as &$taccount)
		{
			$taccount['entrytype'] = 'taccount';
			$acctrefs[$taccount['id']] = &$taccount;

			if ($taccount['parent'] !== null)
			{
				continue; # skip sub accounts
			}

			// show all referenced types
			$type_entry = $refs[$taccount['type']];
			while ($type_entry !== null)
			{
				$displayed_types[] = $type_entry['id'];
				if ($type_entry['parent'] !== null)
				{
					$type_entry = $refs[$type_entry['parent']];
				}
				else # no parent
				{
					$type_entry = null;
				}
			}

			isset($refs[$taccount['type']]['taccounts']) or $refs[$taccount['type']]['taccounts'] = [];
			$refs[$taccount['type']]['taccounts'][$taccount['id']] = $taccount;
		}

		$displayed_types = \array_unique($displayed_types);

		foreach ($taccounts as &$taccount)
		{
			$this->recusively_embed_taccount_handlers($taccount, 'subentries');
		}

		return $tree;
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
	function acctgjournal($journal_id)
	{
		return \app\AcctgJournalLib::entry($journal_id);
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
		$order['type'] = 'desc';

		$operations = \app\AcctgTransactionOperationLib::entries
			(
				$page, $limit, $offset,
				$order, $constraints
			);

		return $operations;
	}

	/**
	 * @return array
	 */
	function acctgtransactionlog()
	{
		return \app\AcctgTransactionLib::entries(null, null, 0, ['timestamp' => 'desc']);
	}

	// ------------------------------------------------------------------------
	// Form Helpers

	/**
	 * @return array taccount types as optgroup array
	 */
	function acctgtypes_options_hierarchy(array $constraints = null,$indenter = null, $typeslabel = null)
	{
		$indenter !== null or $indenter = ' &mdash; ';
		$typeslabel !== null or $typeslabel = \app\Lang::term('TAccount Types:');

		$options = array
			(
				$typeslabel => null,
			);

		$types = static::acctgtypes(null, null, 0, null, $constraints);

		foreach ($types as $type)
		{
			$title = \str_repeat($indenter, $type['depth'] + 1).$type['title'];

			if ($type['usable'])
			{
				$options[$type['id']] = $title;
			}
			else # logical type
			{
				$options[$title] = null;
			}
		}

		return $options;
	}

	/**
	 * Used for splitting multiple option sets
	 *
	 * @return string options devider string
	 */
	protected function options_devider()
	{
		return \str_repeat(' -',32);
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
		$accountslabel !== null or $accountslabel = \app\Lang::term('TAccounts:');

		if ($blanklabel !== false && $blankkey !== false)
		{
			$blanklabel !== null or $blanklabel = '[ '.\app\Lang::term('no account').' ]';
			$blankkey !== null or $blankkey = '';

			$options = array
				(
					$blankkey => $blanklabel,
					$this->options_devider() => [],
					$accountslabel => [],
				);
		}
		else # don't show blank option
		{
			$options = array
				(
					$accountslabel => null,
				);
		}

//		$depthgroups = [ 0 => &$options[$accountslabel] ];
//
//		$taccounts = static::acctgtaccounts(null, null, 0, null, $constraints);
//
//		foreach ($taccounts as $taccount)
//		{
//			if ($taccount['rgt'] - $taccount['lft'] == 1)
//			{
//				$depthgroups[$taccount['depth']][$taccount['id']] = ' &nbsp; '.\str_repeat($indenter, $taccount['depth'] + 1).$taccount['title'];
//			}
//			else # rgt - lft > 1, node has children
//			{
//				$key = ' &nbsp; '.\str_repeat($indenter, $taccount['depth'] + 1).$taccount['title'];
//				$depthgroups[$taccount['depth']][$key] = [];
//				$depthgroups[$taccount['depth'] + 1] = &$depthgroups[$taccount['depth']][$key];
//			}
//		}

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
		$accountslabel !== null or $accountslabel = \app\Lang::term('TAccounts:');

		if ($blanklabel !== false && $blankkey !== false)
		{
			$blanklabel !== null or $blanklabel = '[ '.\app\Lang::term('no account').' ]';
			$blankkey !== null or $blankkey = '';

			$options = array
				(
					$blankkey => $blanklabel,
					$this->options_devider() => null,
					$accountslabel => null,
				);
		}
		else # don't show blank option
		{
			$options = array
				(
					$accountslabel => null,
				);
		}

		$taccounts = static::acctgtaccounts(null, null, 0, null, $constraints);
		$types = static::acctgtypes(null, null);
		$tree = \app\Arr::hierarchy_from(\app\Arr::tablemap($types, 'id'));
		$refs = \app\Arr::refs_from($tree, 'id', 'subentries');

		$displayed_types = [];
		$type_options = [];

		foreach ($taccounts as $taccount)
		{
			// show all referenced types
			$type_entry = $refs[$taccount['type']];
			while ($type_entry !== null)
			{
				$displayed_types[] = $type_entry['id'];
				if ($type_entry['parent'] !== null)
				{
					$type_entry = $refs[$type_entry['parent']];
				}
				else # no parent
				{
					$type_entry = null;
				}
			}

			$option_title = ' &nbsp; '.\str_repeat($indenter, $refs[$taccount['type']]['depth'] + $taccount['depth'] + 2).$taccount['title'];
			isset($type_options[$taccount['type']]) or $type_options[$taccount['type']] = [];
			$type_options[$taccount['type']][$taccount['id']] = $option_title;
		}

		$displayed_types = \array_unique($displayed_types);

		$options = \app\Arr::process_hierarchy
			(
				$tree,
				function (&$result, $entry) use ($type_options, $displayed_types, $indenter)
				{
					if ( ! \in_array($entry['id'], $displayed_types))
					{
						return; # skip rendering
					}

					$option_title = ' &nbsp; '.\str_repeat($indenter, $entry['depth'] + 1).$entry['title'];
					$result[$option_title] = null;
					if (isset($type_options[$entry['id']]))
					{
						foreach ($type_options[$entry['id']] as $key => $option)
						{
							$result[$key] = $option;
						}
					}
				},
				null, # default subentry key
				$options
			);

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
