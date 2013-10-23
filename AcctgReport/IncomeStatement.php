<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   AcctgReport
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgReport_IncomeStatement extends \app\AcctgReport
{
	/**
	 * @return static $this
	 */
	static function instance($options = null, $group = null)
	{
		$i = parent::instance($options, $group);
		$i->set('title', 'Income Statement');
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
					__METHOD__,
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

			foreach ($sql_totals as &$entry)
			{
				$entry['type'] = \app\AcctgTAccountTypeLib::typefortaccount($entry['taccount']);
				$entry['total'] = \intval(\floatval($entry['total']) * 100) * \app\AcctgTAccountTypeLib::sign($entry['type']) * \app\AcctgTAccountLib::sign($entry['taccount']) / 100;
			}

			$totals[$key] = \app\Arr::gatherkeys($sql_totals, 'taccount', 'total');
		}

		// Resolve Income
		// --------------

		$assetstype = \app\AcctgTAccountTypeLib::typebyname('revenue');
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
					// we multiply by -1 to account for Cr/Dr inversion
					$taccount[$key] = \floatval($totals[$key][$taccount['id']]) * (-1);
				}
				else # no total (ie. no operations involving the taccount exist)
				{
					$taccount[$key] = 0.00;
				}
			}
		}

		$incomeview = $this->reportview->newcategory('Income');

		$this->integrate_taccounts
			(
				$incomeview,
				$asset_taccounts
			);

		// Resolve Expenses
		// ----------------

		$expensestype = \app\AcctgTAccountTypeLib::typebyname('expenses');
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

		$expenseview = $this->reportview->newcategory('Expenses');

		$this->integrate_taccounts
			(
				$expenseview,
				$expense_taccounts
			);

		// Totals
		// ------

		$nettotal = [];
		foreach ($incomeview->totals() as $key => $total)
		{
			$nettotal[$key] = \intval($total * 100);
		}

		foreach ($expenseview->totals() as $key => $total)
		{
			$nettotal[$key] -= \intval($total * 100);
		}

		foreach ($nettotal as $key => $total)
		{
			$nettotal[$key] = $nettotal[$key] / 100;
		}

		if ($nettotal['total'] >= 0)
		{
			$this->reportview->newdataentry($nettotal + ['title' => '<b>Net Income</b>']);
		}
		else # balance < 0
		{
			$this->reportview->newdataentry($nettotal + ['title' => '<b>Net Loss</b>']);
		}

		return $this;
	}

} # class
