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
	 * @return static
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
		// create root category
		$this->reportview = \app\AcctgReportCategory::instance();
		$this->reportview->nototals();

		$acctg_input = ['breakdown' => []];

		// Parse report settings
		// ---------------------

		list($date_from, $date_to) = $this->calculate_interval();
		list($keys, $breakdown) = $this->calculate_breakdown($date_from, $date_to, $this->reportview);

		// Calculate Report Data
		// ---------------------

		foreach ($breakdown as $key => &$conf)
		{
			$conf['interval']['from'] = \date_create($conf['interval']['from']);
			$conf['interval']['to'] = \date_create($conf['interval']['to']);
			$acctg_input['breakdown'][$key] = $conf['interval'];
		}

		$entity = \app\AcctgEntity_IncomeStatement::instance($acctg_input, $this->get('group', null));
		$result = $entity->run()->report();

		$totals = $result['data'];

		// set generation time
		$this->set('timestamp', $result['timestamp']);

		// Add report headers
		// ------------------

		$this->headers = [];
		foreach ($breakdown as $segment)
		{
			$this->headers[] = $segment['title'];
		}

		// Resolve Income
		// --------------

		$revenuetypes = \app\AcctgTAccountTypeLib::relatedtypes(\app\AcctgTAccountTypeLib::named('revenue'));
		$income_taccounts = \app\AcctgTAccountLib::tree_hierarchy
			(
				null, null, 0,
				null,
				[
					'entry.group' => $this->get('group', null),
					'entry.type' => [ 'in' => $revenuetypes ],
				]
			);

		$refs_income_accounts = \app\Arr::refs_from($income_taccounts, 'id', 'subentries');

		foreach ($refs_income_accounts as &$taccount)
		{
			foreach ($keys as $key)
			{
				if (isset($totals[$key]['income'][$taccount['id']]))
				{
					// we multiply by -1 to account for Cr/Dr inversion
					$taccount[$key] = \floatval($totals[$key]['income'][$taccount['id']]);
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
				$income_taccounts
			);

		// Resolve Expenses
		// ----------------

		$expensestypes = \app\AcctgTAccountTypeLib::relatedtypes(\app\AcctgTAccountTypeLib::named('expenses'));
		$expense_taccounts = \app\AcctgTAccountLib::tree_hierarchy
			(
				null, null, 0,
				null,
				[
					'entry.group' => $this->get('group', null),
					'entry.type' => [ 'in' => $expensestypes ],
				]
			);

		$refs_expenses_taccounts = \app\Arr::refs_from($expense_taccounts, 'id', 'subentries');

		foreach ($refs_expenses_taccounts as &$taccount)
		{
			foreach ($keys as $key)
			{
				if (isset($totals[$key]['expenses'][$taccount['id']]))
				{
					// we multiply by -1 to account for Cr/Dr inversion
					$taccount[$key] = \floatval($totals[$key]['expenses'][$taccount['id']]);
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

} # class
