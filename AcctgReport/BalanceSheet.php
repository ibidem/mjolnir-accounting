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

		// add column data handlers
		$this->reportview->appendhandler('balance', 'currency');

		// setup view headers
		$from_date = \date_create($this->get('from_date', null));
		$to_date = \date_create($this->get('to_date', null));

		$interval_title
			= $from_date->format('Y-m-d')
			. ' &mdash; '
			. $to_date->format('Y-m-d');

		$this->headers = [ $interval_title ];

		// 1. list all assets

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

		$sql_totals = \app\SQL::prepare
			(
				__METHOD__,
				'
					SELECT op.taccount, SUM(op.amount_value * op.type) total

					  FROM `acctg__transaction_operations` op

					  JOIN `acctg__transactions` tr
					    ON tr.id = op.transaction

				     WHERE tr.group <=> :group
					   AND tr.date BETWEEN :start_date AND :end_date

				     GROUP BY op.taccount
				'
			)
			->date(':start_date', $from_date->format('Y-m-d'))
			->date(':end_date', $to_date->format('Y-m-d'))
			->num(':group', $this->get('group', null))
			->run()
			->fetch_all();

		$totals = \app\Arr::gatherkeys($sql_totals, 'taccount', 'total');

		foreach ($refs_asset_taccounts as &$taccount)
		{
			if (isset($totals[$taccount['id']]))
			{
				$taccount['balance'] = \floatval($totals[$taccount['id']]);
			}
			else # no total (ie. no operations involving the taccount exist)
			{
				$taccount['balance'] = 0.00;
			}
		}

		$incomeview = $this->reportview->newcategory('Income');

		$this->integrate_taccounts
			(
				$incomeview,
				$asset_taccounts
			);

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
		$headerview = '<th>&nbsp;</th>';

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
