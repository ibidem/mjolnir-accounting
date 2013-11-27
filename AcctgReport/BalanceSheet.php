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
	 * @return static
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
		// create root category
		$this->reportview = \app\AcctgReportCategory::instance();
		$this->reportview->nototals();

		$acctg_input = ['breakdown' => []];

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
		foreach ($breakdown as $key => &$conf)
		{
			if ($conf['interval'] !== null)
			{
				$conf['interval']['from'] = \app\AcctgTransactionLib::startoftime();
				$conf['title'] = $conf['interval']['to'];
				$conf['interval']['to'] = \date_create($conf['interval']['to']);
			}

			$acctg_input['breakdown'][$key] = $conf['interval'];
		}

		// Calculate Report Data
		// ---------------------

		$entity = \app\AcctgEntity_BalanceSheet::instance($acctg_input, $this->get('group', null));
		$result = $entity->run()->report();

		// set generation time
		$this->set('timestamp', $result['timestamp']);

		// Add report headers
		// ------------------

		$this->headers = [];
		foreach ($breakdown as $segment)
		{
			$this->headers[] = $segment['title'];
		}

		// Resolve Assets
		// --------------

		$totals = $result['data'];

		$assetstypes = \app\AcctgTAccountTypeLib::named('assets');
		$asset_taccounts = \app\AcctgTAccountLib::tree_hierarchy
			(
				null, null, 0,
				null,
				[
					'entry.group' => $this->get('group', null),
					'entry.type' => [ 'in' => \app\AcctgTAccountTypeLib::relatedtypes($assetstypes) ],
				]
			);

		$refs_asset_taccounts = \app\Arr::refs_from($asset_taccounts, 'id', 'subentries');

		foreach ($refs_asset_taccounts as &$taccount)
		{
			foreach ($keys as $key)
			{
				if (isset($totals[$key]['assets'][$taccount['id']]))
				{
					$taccount[$key] = \floatval($totals[$key]['assets'][$taccount['id']]);
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

		$expensestype = \app\AcctgTAccountTypeLib::named('liabilities');
		$expense_taccounts = \app\AcctgTAccountLib::tree_hierarchy
			(
				null, null, 0,
				null,
				[
					'entry.group' => $this->get('group', null),
					'entry.type' => [ 'in' => \app\AcctgTAccountTypeLib::relatedtypes($expensestype) ],
				]
			);

		$refs_expenses_taccounts = \app\Arr::refs_from($expense_taccounts, 'id', 'subentries');

		foreach ($refs_expenses_taccounts as &$taccount)
		{
			foreach ($keys as $key)
			{
				if (isset($totals[$key]['liabilities'][$taccount['id']]))
				{
					// we multiply by -1 to account for Cr/Dr inversion
					$taccount[$key] = \floatval($totals[$key]['liabilities'][$taccount['id']]) * (-1);
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

		$capital_totals = [];
		foreach ($keys as $key)
		{
			$capital_totals[$key] = $totals[$key]['capital'];
		}

		$liabilities_and_equity->newdataentry(['title' => 'Capital'] + $capital_totals);

		return $this;
	}

} # class
