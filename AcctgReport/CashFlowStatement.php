<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   AcctgReport
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgReport_CashFlowStatement extends \app\AcctgReport
{
	/**
	 * @return static
	 */
	static function instance($options = null, $group = null)
	{
		$i = parent::instance($options, $group);
		$i->set('title', 'Cash Flow Statement');
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
		$this->reportview = \app\AcctgReportCategory::instance('Cash Flow Statement');
		$this->reportview->nototals();

		$acctg_input = ['breakdown' => []];

		// Parse report settings
		// ---------------------

		list($date_from, $date_to) = $this->calculate_interval();
		list($keys, $breakdown) = $this->calculate_breakdown($date_from, $date_to, $this->reportview);

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

		$entity = \app\AcctgEntity_CashFlowStatement::instance($acctg_input, $this->get('group', null));
		$report = $entity->run()->report();
		$reportdata = &$report['data'];

		// set generation time
		$this->set('timestamp', $report['timestamp']);

		// Add report headers
		// ------------------

		$this->headers = [];
		foreach ($breakdown as $segment)
		{
			$this->headers[] = $segment['title'];
		}

		$cat = $keys[0];

		// Operating Activities
		// --------------------

		$operating_activities = $this->reportview->newcategory('Operating Activities');

		// add net income
		$operating_activities->newdataentry
			(
				[
					'title' => 'Net Earnings',
					$cat => $reportdata['operating']['net_earnings'][$cat]
				]
			);

		// add depreciation
		$operating_activities->newdataentry
			(
				[
					'title' => 'Depreciation',
					$cat => $reportdata['operating']['depreciation'][$cat]
				]
			);

		// add adjustments
		foreach ($reportdata['operating']['reconciliation'] as $adjustment)
		{
			$taccount = \app\AcctgTAccountLib::entry($adjustment[$cat]['taccount']);

			$operating_activities->newdataentry
				(
					[
						'title' => ($adjustment[$cat]['type'] == 'decrese' ? 'Decrease in ' : 'Increase in ') . $taccount['title'],
						$cat => $adjustment[$cat]['value']
					]
				);
		}

		// Investment Activities
		// ---------------------

		$investment_activities = $this->reportview->newcategory('Investment Activities');

		$investment_inflows = $investment_activities->newcategory('Inflows');

		foreach ($reportdata['investing']['inflows'] as $adjustment)
		{
				$taccount = \app\AcctgTAccountLib::entry($adjustment[$cat]['taccount']);

				$investment_inflows->newdataentry
					(
						[
							'title' => $taccount['title'],
							$cat => $adjustment[$cat]['value']
						]
					);
		}

		$investment_outflows = $investment_activities->newcategory('Outflows');

		foreach ($reportdata['investing']['outflows'] as $adjustment)
		{
			$taccount = \app\AcctgTAccountLib::entry($adjustment[$cat]['taccount']);

			$investment_outflows->newdataentry
				(
					[
						'title' => $taccount['title'],
						$cat => $adjustment[$cat]['value']
					]
				);
		}

		// Investment Activities
		// ---------------------

		$financing_activities = $this->reportview->newcategory('Financing Activities');

		$financing_inflows = $financing_activities->newcategory('Inflows');

		foreach ($reportdata['financing']['inflows'] as $adjustment)
		{
			$taccount = \app\AcctgTAccountLib::entry($adjustment[$cat]['taccount']);

			$financing_inflows->newdataentry
				(
					[
						'title' => $taccount['title'],
						$cat => $adjustment[$cat]['value']
					]
				);
		}

		$financing_outflows = $financing_activities->newcategory('Outflows');

		foreach ($reportdata['financing']['outflows'] as $adjustment)
		{
			$taccount = \app\AcctgTAccountLib::entry($adjustment[$cat]['taccount']);

			$financing_outflows->newdataentry
				(
					[
						'title' => $taccount['title'],
						$cat => $adjustment[$cat]['value']
					]
				);
		}

		return $this;
	}

} # class
