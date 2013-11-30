<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
abstract class AcctgReport extends \app\Instantiatable implements AcctgReportInterface
{
	use \app\Trait_AcctgReport;

	/**
	 * @return static
	 */
	static function instance($options = null, $group = null)
	{
		$i = parent::instance();

		if ($options !== null)
		{
			foreach ($options as $key => $value)
			{
				$i->set($key, $value);
			}
		}

		$i->set('group', $group);

		return $i;
	}

	/**
	 * Resolves interval filtering. Result is broken down to start and end
	 * date.
	 *
	 * @return array (start, end)
	 */
	protected function calculate_interval()
	{
		$date_from = $this->get('from_date', \date('Y-m-d'));
		! empty($date_from) or $date_from = \date('Y-m-d');

		$date_to = $this->get('to_date', \date('Y-m-d'));
		! empty($date_to) or $date_to = \date('Y-m-d');

		$interval = $this->get('interval', 'custom');
		if ($interval === 'custom')
		{
			$date_from = \date_create_from_format('Y-m-d', $date_from);
			$date_to = \date_create_from_format('Y-m-d', $date_to);
		}
		else if ($interval === 'all')
		{
			$table = \app\SQL::prepare
				(
					'
						SELECT MIN(entry.date) mindate,
						       MAX(entry.date) maxdate
					      FROM `[transactions]` entry
						 WHERE entry.group <=> :group
					',
					[
						'[transactions]' => \app\AcctgTransactionLib::table()
					]
				)
				->num(':group', $this->get('group', null))
				->run()
				->fetch_entry();

			$date_from = \date_create($table['mindate']);
			$date_to = \date_create($table['maxdate'])->modify('+1 day');
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

		return [ $date_from, $date_to ];
	}

	/**
	 * Resolves breakdown filtering.
	 *
	 * @return array (keys, breakdown) in standard format
	 */
	protected function calculate_breakdown($date_from, $date_to, AcctgReportEntryInterface $reportview)
	{
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
			$reportview->appendhandler('total', 'currency');
			$reportview->addcalculator('total', 'currency');

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
				$reportview->appendhandler('total', 'currency');
				$reportview->addcalculator('total', 'currency');

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
					$reportview->appendhandler($key, 'currency');
					$reportview->addcalculator($key, 'currency');

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
				$reportview->appendhandler('total', 'currency');
				$reportview->addcalculator
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

		return [ $keys, $breakdown ];
	}

	/**
	 * Recursively resolve TAccount hierarchy.
	 */
	protected function integrate_taccounts(AcctgReportEntryInterface $root, $taccounts)
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
