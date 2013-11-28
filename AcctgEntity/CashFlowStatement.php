<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   AcctgEntity
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgEntity_CashFlowStatement extends \app\Instantiatable
{
	/** @var array */
	protected $conf = null;

	/** @var array */
	protected $group = null;

	/** @var array */
	protected $report = null;

	/**
	 * @return static
	 */
	static function instance(array $conf = null, $group = null)
	{
		/** @var AcctgEntity_CashFlowStatement $i */
		$i = parent::instance();

		$i->conf = $conf;
		$i->group = $group;

		$i->report = array
			(
				'timestamp' => null,
				'data' => [],
			);

		return $i;
	}

	/**
	 * @return array
	 */
	function report()
	{
		return $this->report;
	}

	/**
	 * Generate the report.
	 *
	 * @return static $this
	 */
	function run()
	{
		$report = &$this->report;
		$conf = &$this->conf;

		// set generation time
		$report['timestamp'] = \date_create();

		// Operating Activities
		// --------------------

		// generate previous year based on pivot point
		$last_years_conf = ['breakdown' => []];

		foreach ($conf['breakdown'] as $cat => &$entry)
		{
			$entry['from'] = \date_create(\app\Acctg::fiscalyear_start_for($entry['from'], $this->group));
			$entry['from']->modify('+1 year');
			$entry['to'] = clone $entry['from'];
			$entry['to']->modify('+1 year');

			$new_entry = ['from' => clone $entry['from'], 'to' => clone $entry['to']];
			$new_entry['from']->modify('-1 year');
			$new_entry['to']->modify('-1 year');

			$last_years_conf['breakdown'][$cat] = $new_entry;
		}

		// last years totals
		$entity = \app\AcctgEntity_BalanceSheet::instance($last_years_conf, $this->group);
		$last_years_balance = $entity->run()->report();

		// this years totals
		$entity = \app\AcctgEntity_BalanceSheet::instance($conf, $this->group);
		$this_years_balance = $entity->run()->report();

		// income statement
		$entity = \app\AcctgEntity_IncomeStatement::instance($conf, $this->group);
		$income_statement = $entity->run()->totals();

		$cashflow_statement = [];

		$taccounts_table = \app\AcctgTAccountLib::table();
		$depreciation_types = \implode(',', \app\AcctgTAccountTypeLib::relatedtypes(\app\AcctgTAccountTypeLib::named('depreciation')));

		foreach (\array_keys($conf['breakdown']) as $cat)
		{
			// We start at net income/loss
			// ---------------------------

			$cashflow_statement[$cat] = array
				(
					'variation' => [],                                           # increase/decrese raw information
				// "Operating Activities"
					'net_earnings' => $income_statement[$cat],                   # net income/loss
					'depreciation' => 0.00,                                      # depreciation adjustments
					'reconciliation' => [],                                      # adjustments to reconcile net income
				// "Investing Activities"
					'investing' => array
						(
							'inflows' => [],
							'outflows' => [],
						),
				// "Financing Activities"
					'financing' => array
						(
							'inflows' => [],
							'outflows' => [],
						),
				);

			// We create a increase/decrease column
			// ------------------------------------

			$startdate = null;
			$enddate = null;
			$acct_balance_sql = \app\SQL::prepare
				(
					__METHOD__.':acct-balance',
					'
						SELECT op.taccount,
							   SUM(op.amount_value * op.type) total

						  FROM `'.\app\AcctgTransactionOperationLib::table().'` op

						  JOIN `'.\app\AcctgTransactionLib::table().'` tr
							ON tr.id = op.transaction

						 WHERE tr.group <=> :group
						   AND unix_timestamp(tr.date) >= unix_timestamp(:start_date)
						   AND unix_timestamp(tr.date) < unix_timestamp(:end_date)

						 GROUP BY op.taccount
						 -- debug
					'
				)
				->num(':group', $this->group)
				->bindstr(':start_date', $startdate)
				->bindstr(':end_date', $enddate);

			$startdate = \app\AcctgTransactionLib::startoftime()->format('Y-m-d');
			$pivot = clone $conf['breakdown'][$cat]['to'];
			$enddate = clone $pivot;
			$enddate->modify('-1 year');
			$enddate = $enddate->format('Y-m-d');
			$prev_year_accts = \app\Arr::gatherkeys($acct_balance_sql->run()->fetch_all(), 'taccount', 'total');

			$enddate = $pivot->format('Y-m-d');
			$this_year_accts = \app\Arr::gatherkeys($acct_balance_sql->run()->fetch_all(), 'taccount', 'total');

			$accts = \array_unique(\array_merge(\array_keys($prev_year_accts), \array_keys($this_year_accts)));

			foreach ($accts as $acct)
			{
				isset($prev_year_accts[$acct]) or $prev_year_accts[$acct] = 0;
				isset($this_year_accts[$acct]) or $this_year_accts[$acct] = 0;
				$cashflow_statement[$cat]['variation'][$acct]
					= ($this_year_accts[$acct] - $prev_year_accts[$acct])
					* \app\AcctgTAccountLib::treesign($acct)
					* (\app\AcctgTAccountTypeLib::is_equity_acct($acct) ? -1 : +1);
			}

			// Add depreciation accounts
			// -------------------------

			$depreciation_accts = \app\SQL::prepare
				(
					__METHOD__,
					"
						SELECT entry.*
	                      FROM `$taccounts_table` entry

						 WHERE entry.group <=> :group
						   AND entry.type IN ($depreciation_types)
					"
				)
				->num(':group', $this->group)
				->run()
				->fetch_all();

			// calculate total
			$depreciation_adjust_cents = 0;
			foreach ($depreciation_accts as $entry)
			{
				if (isset($cashflow_statement[$cat]['variation'][$entry['id']]))
				{
					$depreciation_adjust_cents -= \intval($cashflow_statement[$cat]['variation'][$entry['id']] * 100) * \app\AcctgTAccountLib::treesign($entry['id']);
				}
			}

			// add from net income depereciation since it didnt change cash
			$cashflow_statement[$cat]['depreciation'] = $depreciation_adjust_cents / 100;

			// Add Current Assets & Current Liabilities Changes
			// ------------------------------------------------

			#
			# The following uses the window method, as expressed by the following diagram
			#
			#                            Increase    Decrease
			#            Current Assets     -          +
			#       Current Liabilities     +          -
			#

			// get all current assets accounts
			$current_assets_types = \app\AcctgTAccountTypeLib::relatedtypes(\app\AcctgTAccountTypeLib::named('current-assets'));
			$current_liabilities_types = \app\AcctgTAccountTypeLib::relatedtypes(\app\AcctgTAccountTypeLib::named('current-liabilities'));

			foreach ($cashflow_statement[$cat]['variation'] as  $acct => $val)
			{
				$taccount = \app\AcctgTAccountLib::entry($acct);

				if (\in_array($taccount['type'], $current_assets_types))
				{
					if ($val > 0)
					{
						$adjustment = array
							(
								'type' => 'increase',
								'taccount' => $acct,
								'value' => -1 * $val
							);

						$cashflow_statement[$cat]['reconciliation'][] = $adjustment;
					}
					else if ($val < 0)
					{
						$adjustment = array
							(
								'type' => 'decrese',
								'taccount' => $acct,
								'value' => $val
							);

						$cashflow_statement[$cat]['reconciliation'][] = $adjustment;
					}
					# else: ignore 0
				}
				else if (\in_array($taccount['type'], $current_liabilities_types))
				{
					if ($val > 0)
					{
						$adjustment = array
							(
								'type' => 'increase',
								'taccount' => $acct,
								'value' => $val
							);

						$cashflow_statement[$cat]['reconciliation'][] = $adjustment;
					}
					else if ($val < 0)
					{
						$adjustment = array
							(
								'type' => 'decrese',
								'taccount' => $acct,
								'value' => -1 * $val
							);

						$cashflow_statement[$cat]['reconciliation'][] = $adjustment;
					}
					# else: ignore 0.00
				}
			}

			// Investing Activities
			// --------------------

			// get all current assets accounts
			$longterm_assets_types = \app\AcctgTAccountTypeLib::relatedtypes(\app\AcctgTAccountTypeLib::named('long-term-assets'));

			foreach ($cashflow_statement[$cat]['variation'] as  $acct => $val)
			{
				$taccount = \app\AcctgTAccountLib::entry($acct);

				if (\in_array($taccount['type'], $longterm_assets_types))
				{
					if ($val < 0)
					{
						$adjustment = array
							(
								'taccount' => $acct,
								'value' => $val,
							);

						$cashflow_statement[$cat]['investing']['inflows'][] = $adjustment;
					}
					else if ($val > 0)
					{
						$adjustment = array
							(
								'taccount' => $acct,
								'value' => $val,
							);

						$cashflow_statement[$cat]['investing']['outflows'][] = $adjustment;
					}
					# else: ignore 0.00
				}
			}

			// Financing Activities
			// --------------------

			// get all current assets accounts
			$withdrawl_types = \app\AcctgTAccountTypeLib::relatedtypes(\app\AcctgTAccountTypeLib::named('withdrawals'));
			$ownerequity_types = \app\AcctgTAccountTypeLib::relatedtypes(\app\AcctgTAccountTypeLib::named('owner-equity'));
			$investment_types = \array_diff($ownerequity_types, $withdrawl_types);

			foreach ($cashflow_statement[$cat]['variation'] as  $acct => $val)
			{
				$taccount = \app\AcctgTAccountLib::entry($acct);

				if (\in_array($taccount['type'], $withdrawl_types))
				{
					$adjustment = array
						(
							'taccount' => $acct,
							'value' => $val,
						);

					$cashflow_statement[$cat]['financing']['outflows'][] = $adjustment;
				}
				else if (\in_array($taccount['type'], $investment_types))
				{
					$adjustment = array
						(
							'taccount' => $acct,
							'value' => $val,
						);

					$cashflow_statement[$cat]['financing']['inflows'][] = $adjustment;
				}
			}

			// perform check with cash; cash must be equal to the total of all
			// 3 activities combined; any difference is perceived as an
			// operational error and the statement will be promptly rejected
			// though an NotApplicable exception

			// perform cash total
			$cash_total_cents = 0;
			$cash_types = \app\AcctgTAccountTypeLib::relatedtypes(\app\AcctgTAccountTypeLib::named('cash'));

			foreach ($cashflow_statement[$cat]['variation'] as  $acct => $val)
			{
				$taccount = \app\AcctgTAccountLib::entry($acct);

				if (\in_array($taccount['type'], $cash_types))
				{
					$cash_total_cents = \intval($val * 100);
				}
			}

			// perform operations total
			$operations_total_cents = \intval($cashflow_statement[$cat]['net_earnings'] * 100);
			$operations_total_cents += \intval($cashflow_statement[$cat]['depreciation'] * 100);
			foreach ($cashflow_statement[$cat]['reconciliation'] as $adjustment)
			{
				$operations_total_cents += \intval($adjustment['value'] * 100);
			}

			// perform investing total
			$investing_total_cents = 0;
			foreach ($cashflow_statement[$cat]['investing']['inflows'] as $adjustment)
			{
				$investing_total_cents += \intval($adjustment['value'] * 100);
			}
			foreach ($cashflow_statement[$cat]['investing']['outflows'] as $adjustment)
			{
				$investing_total_cents += \intval($adjustment['value'] * 100);
			}

			// perform financing total
			$financing_total_cents = 0;
			foreach ($cashflow_statement[$cat]['financing']['inflows'] as $adjustment)
			{
				$investing_total_cents += \intval($adjustment['value'] * 100);
			}
			foreach ($cashflow_statement[$cat]['financing']['outflows'] as $adjustment)
			{
				$investing_total_cents += \intval($adjustment['value'] * 100);
			}

			$check_sum = $operations_total_cents + $investing_total_cents + $financing_total_cents;
			if ($cash_total_cents != $check_sum)
			{
				throw new \app\Exception_NotApplicable('Correctness checks failed. Cash Flow Statement has been rejected for being incorrect (expected cash flow '.($cash_total_cents / 100).', but statement showed '.($check_sum / 100).'). This may be do to an error in the system or your accounts. Please contact an administrator or technical support to help resolve the issue.');
			}
		}

		$this->report['data'] = $this->category_inversion($cashflow_statement);

		return $this;
	}

	protected function category_inversion($cashflow_statement)
	{
		// we need to move the category inside to comply with how the system
		// deals with data-breakdowns in all other circumstances

		$reportdata = array
			(
				'operating' => array
					(
						'net_earnings' => [],
						'depreciation' => [],
						'reconciliation' => [],
					),
				'investing' => array
					(
						'inflows' => [],
						'outflows' => [],
					),
				'financing' => array
					(
						'inflows' => [],
						'outflows' => [],
					),
			);

		$cols = \array_keys($cashflow_statement);

		if (\count($cols) != 1)
		{
			\mjolnir\log('Info', 'User tried to get breakdown ['.\implode(',', \array_keys($cashflow_statement)).'] for cash flow statement.');
			throw new \app\Exception_NotApplicable('Operation not supported at this time.');
		}

		$col = \array_pop($cols);

		// Operating Activities
		$reportdata['operating']['net_earnings'] = [ $col => $cashflow_statement[$col]['net_earnings']];
		$reportdata['operating']['depreciation'] = [ $col => $cashflow_statement[$col]['depreciation']];

		foreach ($cashflow_statement[$col]['reconciliation'] as $adjustment)
		{
			$reportdata['operating']['reconciliation'][] = [ $col => $adjustment ];
		}

		// Investing Activities
		foreach ($cashflow_statement[$col]['investing']['inflows'] as $adjustment)
		{
			$reportdata['investing']['inflows'][] = [ $col => $adjustment ];
		}
		foreach ($cashflow_statement[$col]['investing']['outflows'] as $adjustment)
		{
			$reportdata['investing']['outflows'][] = [ $col => $adjustment ];
		}

		// Financing Activities
		foreach ($cashflow_statement[$col]['financing']['inflows'] as $adjustment)
		{
			$reportdata['financing']['inflows'][] = [ $col => $adjustment ];
		}
		foreach ($cashflow_statement[$col]['financing']['outflows'] as $adjustment)
		{
			$reportdata['financing']['outflows'][] = [ $col => $adjustment ];
		}

		return $reportdata;
	}

} # class
