<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   AcctgReport
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgReport_OwnerEquity extends \app\AcctgReport
{
	/**
	 * @return static
	 */
	static function instance($options = null, $group = null)
	{
		$i = parent::instance($options, $group);
		$i->set('title', 'Statement of Owner\'s Equity');
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

		$entity = \app\AcctgEntity_OwnerEquity::instance($acctg_input, $this->get('group', null));
		$result = $entity->run()->report();

		$rawtotals = $result['data'];

		// set generation time
		$this->set('timestamp', $result['timestamp']);

		// Add report headers
		// ------------------

		$this->headers = [];
		foreach ($breakdown as $segment)
		{
			$this->headers[] = $segment['title'];
		}

		$prev_capital = [];
		$investments = [];
		$withdrawals = [];
		$net_total = [];
		$ending_capital = [];

		foreach ($keys as $key)
		{
			$prev_capital[$key] = $rawtotals[$key]['capital'];

			$sum = 0;
			foreach ($rawtotals[$key]['investments'] as $entry)
			{
				$sum += \intval($entry * 100);
			}

			$investments[$key] = $sum / 100;

			$sum = 0;
			foreach ($rawtotals[$key]['withdrawals'] as $entry)
			{
				$sum += \intval($entry * 100);
			}

			$withdrawals[$key] = $sum / 100;

			$net_total[$key] = $rawtotals[$key]['net_total'];
			$ending_capital[$key] = $rawtotals[$key]['ending_capital'];
		}

		$this->reportview->newdataentry($prev_capital + ['title' => 'Previous Capital']);
		$this->reportview->newdataentry($investments + ['title' => 'Investments for period']);
		$this->reportview->newdataentry($net_total + ['title' => 'Net Income']);
		$this->reportview->newdataentry($withdrawals + ['title' => 'Withdrawls']);
		$this->reportview->newdataentry($ending_capital + ['title' => 'Ending Capital']);

		return $this;
	}

} # class
