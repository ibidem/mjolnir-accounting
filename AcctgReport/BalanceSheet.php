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
		$this->reportview = \app\AcctgReportCategory::instance('Ordinary Income/Expense');
		$this->reportview->nototals();

		$interval = $this->get('interval', 'custom');
		if ($interval === 'custom')
		{
			$date_from = \date_create_from_format('Y-m-d', $this->get('from_date', null));
			$date_to = \date_create_from_format('Y-m-d', $this->get('to_date', null));
		}
		else if ($interval === 'all')
		{
			$table = \app\SQL::prepare
				(
					__METHOD__.':find_earliest_date',
					'
						SELECT MIN(entry.date) mindate,
						       MAX(entry.date) maxdate
					      FROM `'.\app\AcctgTransactionLib::table().'` entry
						 WHERE entry.group <=> :group
					'
				)
				->num(':group', $this->get('group', null))
				->run()
				->fetch_entry();

			$date_from = \date_create($table['mindate']);
			$date_to = \date_create($table['maxdate']);
		}
		else if ($interval === 'today')
		{
			$date_from = \date_create();
			$date_to = \date_create();
			$this->set('breakdown', 'totals-only');
		}
		else if ($interval === 'current-month')
		{
			$date_from = \date_create(\date('Y-m-1'));
			$date_to = \date_create(\date('Y-m-1'))->modify('last day of this month');
			$this->set('breakdown', 'totals-only');
		}
		else # unsuported interval
		{
			throw new \app\Exception('Unknown interval type ['.$interval.']');
		}

		$breakdown = $this->get('breakdown', 'totals-only');

		$from = $date_from->format('Y-m-d');
		$to = $date_to->format('Y-m-d');
		if ($breakdown === 'totals-only')
		{
			if ($from != $to)
			{
				$title = "$from&mdash;$to";
			}
			else # dates are identical
			{
				$title = $from;
			}

			$breakdown = array
				(
					'total' => array
						(
							'title' => $title,
							'interval' => array
								(
									'from' => $from,
									'to' => $to
								),
						),
				);

			// add column data handlers
			$this->reportview->appendhandler('total', 'currency');
			$this->reportview->addcalculator('total', 'currency');

			$keys = [ 'total' ];
		}
		else if ($breakdown === 'month')
		{
			// %a = total number of days
			$diff = \intval($date_to->diff($date_from)->format("%a"));

			$daysinmonth = \cal_days_in_month(CAL_GREGORIAN, $date_from->format('m'), $date_from->format('Y'));

			if ($diff < $daysinmonth)
			{
				if ($from != $to)
				{
					$title = "$from&mdash;$to";
				}
				else # dates are identical
				{
					$title = $from;
				}

				$breakdown = array
					(
						'total' => array
							(
								'title' => $title,
								'interval' => array
									(
										'from' => $from,
										'to' => $to
									),
							),
					);

				// add column data handlers
				$this->reportview->appendhandler('total', 'currency');
				$this->reportview->addcalculator('total', 'currency');

				$keys = [ 'total' ];
			}
			else # diff >= $daysinmonth
			{
				$idx = 0;

				$pivot = clone $date_from;
				$daysinmonth = \cal_days_in_month(CAL_GREGORIAN, $pivot->format('m'), $pivot->format('Y'));
				$breakdown = [];
				do
				{
					if (\intval($pivot->format('d')) === 1)
					{
						$end = \date_create_from_format('Y-m-d', $pivot->format('Y-m-').$daysinmonth);

						if ($end >= $date_to)
						{
							$title = $pivot->format('M \'y, ').$pivot->format('d').'&mdash;'.$date_to->format('d');
							$lastday = $date_to->format('Y-m-d');
						}
						else # end < date_to
						{
							$title = $pivot->format('M \'y');
							$lastday = $end->format('Y-m-d');
						}
					}
					else # inexact month
					{
						$end = \date_create_from_format('Y-m-d', $pivot->format('Y-m-').$daysinmonth);

						if ($end < $date_to)
						{
							$title = $pivot->format('M \'y, ').$pivot->format('d').'&mdash;'.$end->format('d');
							$lastday = $end->format('Y-m-d');
						}
						else # end >= date_to
						{
							$title = $pivot->format('M \'y, ').$pivot->format('d').'&mdash;'.$date_to->format('d');
							$lastday = $date_to->format('Y-m-d');
						}
					}

					$key = 'date'.$idx;
					$keys[] = $key;
					$breakdown[$key] = array
						(
							'title' => $title,
							'interval' => array
								(
									'from' => $pivot->format('Y-m-d'),
									'to' => $lastday
								),
						);

					// add column data handlers
					$this->reportview->appendhandler($key, 'currency');
					$this->reportview->addcalculator($key, 'currency');

					$idx += 1;

					$pivot->modify('first day of next month');
				}
				while ($pivot <= $date_to);

				$breakdown['total'] = array
					(
						'title' => 'Total',
						'interval' => null,
					);

				// add column data handlers
				$this->reportview->appendhandler('total', 'currency');
				$this->reportview->addcalculator
					(
						'total',
						function ($k, AcctgReportDataInterface $dataentry) use ($keys)
						{
							$total = 0;

							$nestedentries = $dataentry->entries();

							if ( ! empty($nestedentries))
							{
								foreach ($nestedentries as $entry)
								{
									foreach ($keys as $datekey)
									{
										$total += \intval($entry->calculate($datekey) * 100);
									}
								}
							}
							else # not empty
							{
								foreach ($keys as $datakey)
								{
									$total += \intval($dataentry->calculate($datakey) * 100);
								}
							}

							return $total / 100;
						}
					);
			}
		}
		else # unsuported breakdown type
		{
			throw new \app\Exception('Unknown breakdown type ['.$breakdown.']');
		}

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
						SELECT op.taccount, SUM(op.amount_value * op.type) total

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

			$totals[$key] = \app\Arr::gatherkeys($sql_totals, 'taccount', 'total');
		}

		// Resolve Income
		// --------------

		$assetstype = \app\AcctgTAccountTypeLib::typebyname('current-assets');
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
					$taccount[$key] = \floatval($totals[$key][$taccount['id']]);
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
			$nettotal[$key] += \intval($total * 100);
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

	/**
	 * Recursively resolve TAccount hierarchy.
	 */
	function integrate_taccounts(AcctgReportEntryInterface $root, $taccounts)
	{
		if ( ! empty($taccounts))
		{
			foreach ($taccounts as $taccount)
			{
				$acctentry = $root->newdataentry($taccount);
				$this->integrate_taccounts($acctentry, $taccount['subentries']);
			}
		}
	}

	/**
	 * @return string
	 */
	function render_header()
	{
		$headerview = '<th class="acctg-report--placeholder">&nbsp;</th>';

		if (empty($this->headers))
		{
			return $headerview;
		}

		foreach ($this->headers as $header)
		{
			$headerview .= "<th>$header</th>";
		}

		return $headerview;
	}

} # class
