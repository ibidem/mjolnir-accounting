<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   AcctgReport
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgReport_BalanceSheet extends \app\AcctgReport
{
	/**
	 * @return static $this
	 */
	static function instance($options = null, $group = null)
	{
		$i = parent::instance($options, $group);
		$i->set('title', 'Balance Sheet');
		return $i;
	}

	/**
	 * @return \mjolnir\types\Validator
	 */
	function validator()
	{
		return static::default_rules()
			->rule('title', 'not_empty');
	}

	/**
	 * Generate the report.
	 *
	 * @return static $this
	 */
	function run()
	{
		// set generation time
		$this->set('timestamp', \time());

		// create root category
		$this->reportview = \app\AcctgReportCategory::instance();
		$this->reportview->nototals();

		// Parse report settings
		// ---------------------

		list($date_from, $date_to) = $this->calculate_interval();
		list($keys, $breakdown) = $this->calculate_breakdown($date_from, $date_to, $this->reportview);

		#
		# The balance sheet is always done for a fixed point in time, so we
		# only really use the "to" date value; since from is always fixed to
		# "from the begining of recording" and hence doesn't matter as much.
		#

		// we need to adjust the breakdown to reflect this
		foreach ($breakdown as &$conf)
		{
			if ($conf['interval'] !== null)
			{
				$conf['interval']['from'] = \app\AcctgTransactionLib::startoftime();
				$conf['title'] = $conf['interval']['to'];
			}
		}

		// Add report headers
		// ------------------

		$this->headers = [];
		foreach ($breakdown as $segment)
		{
			$this->headers[] = $segment['title'];
		}

		// Retrieve entries
		// ----------------

		foreach ($breakdown as $key => $conf)
		{
			if ($conf['interval'] === null)
			{
				continue;
			}

			$sql_totals = \app\SQL::prepare
				(
					__METHOD__.'account-totals',
					'
						SELECT op.taccount,
							   SUM(op.amount_value * op.type) total

						  FROM `'.\app\AcctgTransactionOperationLib::table().'` op

						  JOIN `'.\app\AcctgTransactionLib::table().'` tr
							ON tr.id = op.transaction

						 WHERE tr.group <=> :group
						   AND tr.date BETWEEN :start_date AND :end_date

						 GROUP BY op.taccount
					'
				)
				->date(':start_date', $conf['interval']['from'])
				->date(':end_date', $conf['interval']['to'])
				->num(':group', $this->get('group', null))
				->run()
				->fetch_all();

			foreach ($sql_totals as $entry)
			{
				$entry['type'] = \app\AcctgTAccountTypeLib::typefortaccount($entry['taccount']);
				$entry['total'] = $entry['total'] * \app\AcctgTAccountTypeLib::sign($entry['type']) * \app\AcctgTAccountLib::sign($entry['taccount']);
			}

			$totals[$key] = \app\Arr::gatherkeys($sql_totals, 'taccount', 'total');
		}

		// Resolve Assets
		// --------------

		$assetstype = \app\AcctgTAccountTypeLib::typebyname('assets');
		$asset_taccounts = \app\AcctgTAccountLib::tree_hierarchy
			(
				null, null, 0,
				null,
				[
					'entry.group' => $this->get('group', null),
					'entry.type' => [ 'in' => \app\AcctgTAccountTypeLib::inferred_types($assetstype) ],
				]
			);

		$refs_asset_taccounts = \app\Arr::refs_from($asset_taccounts, 'id', 'subentries');

		foreach ($refs_asset_taccounts as &$taccount)
		{
			foreach ($keys as $key)
			{
				if (isset($totals[$key][$taccount['id']]))
				{
					$taccount[$key] = \floatval($totals[$key][$taccount['id']]);
				}
				else # no total (ie. no operations involving the taccount exist)
				{
					$taccount[$key] = 0.00;
				}
			}
		}

		$incomeview = $this->reportview->newcategory('Assets');

		$this->integrate_taccounts
			(
				$incomeview,
				$asset_taccounts
			);

		// Wrapper
		// -------

		$liabilities_and_equity = $this->reportview->newcategory('Liabilities &amp; Equity');

		// Resolve Liabilities
		// -------------------

		$expensestype = \app\AcctgTAccountTypeLib::typebyname('liabilities');
		$expense_taccounts = \app\AcctgTAccountLib::tree_hierarchy
			(
				null, null, 0,
				null,
				[
					'entry.group' => $this->get('group', null),
					'entry.type' => [ 'in' => \app\AcctgTAccountTypeLib::inferred_types($expensestype) ],
				]
			);

		$refs_expenses_taccounts = \app\Arr::refs_from($expense_taccounts, 'id', 'subentries');

		foreach ($refs_expenses_taccounts as &$taccount)
		{
			foreach ($keys as $key)
			{
				if (isset($totals[$key][$taccount['id']]))
				{
					// we multiply by -1 to account for Cr/Dr inversion
					$taccount[$key] = \floatval($totals[$key][$taccount['id']]) * (-1);
				}
				else # no total (ie. no operations involving the taccount exist)
				{
					$taccount[$key] = 0.00;
				}
			}
		}

		$liabilitiesview = $liabilities_and_equity->newcategory('Liabilities');

		$this->integrate_taccounts
			(
				$liabilitiesview,
				$expense_taccounts
			);

		// Resolve Capital
		// ---------------



		#
		# Capital is calculated as total from Statement of Owner's Equity,
		# which sums up to:
		#
		# ((Captial Stock + Investments + Retained Earnings) @ start of year)
		#	+ Investments total + (Income Statement Total) - Withdrawls
		#

		// Total Start of Year OE
		// ----------------------

		$investments_type = \app\AcctgTAccountTypeLib::find_entry(['slugid' => 'investments']);
		$capitalstock_type = \app\AcctgTAccountTypeLib::find_entry(['slugid' => 'capital-stock']);
		$retained_earnings_type = \app\AcctgTAccountTypeLib::find_entry(['slugid' => 'retained-earnings']);

		// retrieve all relevant capital accounts for start of year calculations
		$sql_totals = \app\SQL::prepare
			(
				__METHOD__.':investments-captalstock-retained-earnings-accounts',
				'
					SELECT taccount.id taccount,
					       type.id type,
					       SUM(op.amount_value * op.type) total

				      FROM `acctg__taccounts` taccount

				      JOIN `acctg__transaction_operations` op
					    ON taccount.id = op.taccount

				      JOIN `acctg__transactions` tr
					    ON op.transaction = tr.id

				      JOIN `acctg__taccount_types` type
					    ON type.id = taccount.type

				     WHERE tr.date < :start_of_year
                       AND tr.group <=> :group
					   AND
					   (
							(type.lft >= :capital_stock_lft AND type.rgt <= :capital_stock_rgt)
							OR
							(type.lft >= :investments_lft AND type.rgt <= :investments_rgt)
							OR
							(type.lft >= :retained_earnings_lft AND type.rgt <= :retained_earnings_rgt)
					   )

					 GROUP BY taccount.id
				'
			)
			->num(':capital_stock_lft', $capitalstock_type['lft'])
			->num(':capital_stock_rgt', $capitalstock_type['rgt'])
			->num(':investments_lft', $investments_type['lft'])
			->num(':investments_rgt', $investments_type['rgt'])
			->num(':retained_earnings_lft', $retained_earnings_type['lft'])
			->num(':retained_earnings_rgt', $retained_earnings_type['rgt'])
			->num(':group', $this->get('group', null))
			->date(':start_of_year', \app\Acctg::fiscalyear_start_for($date_to, $this->get('group', null)))
			->run()
			->fetch_all();

		// account for value sign
		$start_of_year_capital_cents = 0;
		foreach ($sql_totals as &$entry)
		{
			$entry['types'] = \app\AcctgTAccountTypeLib::alltypesfortaccount($entry['taccount']);
			// we multiply by -1 to adjust Dr/Cr to show positive instead of negative values
			$entry['total'] = $entry['total'] * \app\AcctgTAccountTypeLib::sign($entry['type']) * \app\AcctgTAccountLib::sign($entry['taccount']) * (-1);
			$start_of_year_capital_cents += \intval($entry['total'] * 100);
		}

		// retrieve investment accounts
		$sql_totals = \app\SQL::prepare
			(
				__METHOD__.':investments-accounts-for-the-period',
				'
					SELECT taccount.id taccount,
					       type.id type,
					       SUM(op.amount_value * op.type) total

				      FROM `acctg__taccounts` taccount

				      JOIN `acctg__transaction_operations` op
					    ON taccount.id = op.taccount

				      JOIN `acctg__transactions` tr
					    ON op.transaction = tr.id

				      JOIN `acctg__taccount_types` type
					    ON type.id = taccount.type

				     WHERE tr.date >= :start_of_year
                       AND tr.group <=> :group
					   AND type.lft >= :investments_lft
					   AND type.rgt <= :investments_rgt

					 GROUP BY taccount.id
				'
			)
			->num(':investments_lft', $investments_type['lft'])
			->num(':investments_rgt', $investments_type['rgt'])
			->num(':group', $this->get('group', null))
			->date(':start_of_year', \app\Acctg::fiscalyear_start_for($date_to, $this->get('group', null)))
			->run()
			->fetch_all();

		// account for value sign
		$investments_for_period_cents = 0;
		foreach ($sql_totals as &$entry)
		{
			$entry['types'] = \app\AcctgTAccountTypeLib::alltypesfortaccount($entry['taccount']);
			// we multiply by -1 to adjust Dr/Cr to show positive instead of negative values
			$entry['total'] = $entry['total'] * \app\AcctgTAccountTypeLib::sign($entry['type']) * \app\AcctgTAccountLib::sign($entry['taccount']) * (-1);
			$investments_for_period_cents += \intval($entry['total'] * 100);
		}

		// Calculate Income Statement Totals
		// --------------------------------------------------------------------

		$revenue = \app\AcctgTAccountTypeLib::find_entry(['slugid' => 'revenue']);

		$sql_totals_revenue = \app\SQL::prepare
			(
				__METHOD__,
				'
					SELECT taccount.id taccount,
					       type.id type,
					       SUM(op.amount_value * op.type) total

				      FROM `acctg__taccounts` taccount

				      JOIN `acctg__transaction_operations` op
					    ON taccount.id = op.taccount

				      JOIN `acctg__transactions` tr
					    ON op.transaction = tr.id

				      JOIN `acctg__taccount_types` type
					    ON type.id = taccount.type

				     WHERE tr.date >= :start_of_year
                       AND tr.group <=> :group
					   AND type.lft >= :revenue_lft
					   AND type.rgt <= :revenue_rgt

					 GROUP BY taccount.id
				'
			)
			->num(':revenue_lft', $revenue['lft'])
			->num(':revenue_rgt', $revenue['rgt'])
			->num(':group', $this->get('group', null))
			->date(':start_of_year', \app\Acctg::fiscalyear_start_for($date_to, $this->get('group', null)))
			->run()
			->fetch_all();

		$income_statement_totals_cents = 0;
		foreach ($sql_totals_revenue as &$entry)
		{
			$entry['type'] = \app\AcctgTAccountTypeLib::typefortaccount($entry['taccount']);
			// we multiply by -1 to adjust Cr/Dr to positive value representation
			$entry['total'] = \intval(\floatval($entry['total']) * 100) * \app\AcctgTAccountTypeLib::sign($entry['type']) * \app\AcctgTAccountLib::sign($entry['taccount']) * (-1) / 100;
			$income_statement_totals_cents += \intval($entry['total'] * 100);
		}

		$expenses = \app\AcctgTAccountTypeLib::find_entry(['slugid' => 'expenses']);

		$sql_totals_expenses = \app\SQL::prepare
			(
				__METHOD__,
				'
					SELECT taccount.id taccount,
					       type.id type,
					       SUM(op.amount_value * op.type) total

				      FROM `acctg__taccounts` taccount

				      JOIN `acctg__transaction_operations` op
					    ON taccount.id = op.taccount

				      JOIN `acctg__transactions` tr
					    ON op.transaction = tr.id

				      JOIN `acctg__taccount_types` type
					    ON type.id = taccount.type

				     WHERE tr.date >= :start_of_year
                       AND tr.group <=> :group
					   AND type.lft >= :revenue_lft
					   AND type.rgt <= :revenue_rgt

					 GROUP BY taccount.id
				'
			)
			->num(':revenue_lft', $expenses['lft'])
			->num(':revenue_rgt', $expenses['rgt'])
			->num(':group', $this->get('group', null))
			->date(':start_of_year', \app\Acctg::fiscalyear_start_for($date_to, $this->get('group', null)))
			->run()
			->fetch_all();

		$income_statement_totals_cents = 0;
		foreach ($sql_totals_expenses as &$entry)
		{
			$entry['type'] = \app\AcctgTAccountTypeLib::typefortaccount($entry['taccount']);
			// we multiply by -1 to adjust Cr/Dr to positive value representation
			$entry['total'] = \intval(\floatval($entry['total']) * 100) * \app\AcctgTAccountTypeLib::sign($entry['type']) * \app\AcctgTAccountLib::sign($entry['taccount']) * (-1) / 100;
			$income_statement_totals_cents -= \intval($entry['total'] * 100);
		}

		$draws = \app\AcctgTAccountTypeLib::find_entry(['slugid' => 'withdraws']);

		$sql_totals_draws = \app\SQL::prepare
			(
				__METHOD__,
				'
					SELECT taccount.id taccount,
					       type.id type,
					       SUM(op.amount_value * op.type) total

				      FROM `acctg__taccounts` taccount

				      JOIN `acctg__transaction_operations` op
					    ON taccount.id = op.taccount

				      JOIN `acctg__transactions` tr
					    ON op.transaction = tr.id

				      JOIN `acctg__taccount_types` type
					    ON type.id = taccount.type

				     WHERE tr.date >= :start_of_year
                       AND tr.group <=> :group
					   AND type.lft >= :revenue_lft
					   AND type.rgt <= :revenue_rgt

					 GROUP BY taccount.id
				'
			)
			->num(':revenue_lft', $draws['lft'])
			->num(':revenue_rgt', $draws['rgt'])
			->num(':group', $this->get('group', null))
			->date(':start_of_year', \app\Acctg::fiscalyear_start_for($date_to, $this->get('group', null)))
			->run()
			->fetch_all();

		$draws_totals_cents = 0;
		foreach ($sql_totals_draws as &$entry)
		{
			$entry['type'] = \app\AcctgTAccountTypeLib::typefortaccount($entry['taccount']);
			// we multiply by -1 to adjust Cr/Dr to positive value representation
			$entry['total'] = \intval(\floatval($entry['total']) * 100) * \app\AcctgTAccountTypeLib::sign($entry['type']) * \app\AcctgTAccountLib::sign($entry['taccount']) * (-1) / 100;
			$draws_totals_cents += \intval($entry['total'] * 100);
		}

		$capital_cents = $start_of_year_capital_cents + $investments_for_period_cents + $income_statement_totals_cents - $draws_totals_cents;

		$liabilities_and_equity->newdataentry(['title' => 'Capital', 'total' => $capital_cents / 100]);

		return $this;
	}

} # class
