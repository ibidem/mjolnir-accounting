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
	 * @return static $this
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

		// main categories
		$operating_activities = $this->reportview->newcategory('Operating Activities');
		$investment_activities = $this->reportview->newcategory('Investment Activities');
		$financing_activities = $this->reportview->newcategory('Financing Activities');



		return $this;
	}

} # class
