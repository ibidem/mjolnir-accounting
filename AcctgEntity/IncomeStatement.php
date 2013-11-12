<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   AcctgEntity
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgEntity_IncomeStatement extends \app\Instantiatable
{
	/** @var array */
	protected $conf = null;

	/** @var array */
	protected $group = null;

	/**
	 * @return static
	 */
	static function instance(array $conf = null, $group = null)
	{
		$i = parent::instance();

		$i->conf = $conf;
		$i->group = $group;

		$i->report = array
			(
				'timestamp' => null,
				'data' => [],
			);

		return $i;
	}

	/**
	 * @return array
	 */
	function report()
	{
		return $this->report;
	}

	/**
	 * Generate the report.
	 *
	 * @return static $this
	 */
	function run()
	{
		$report = &$this->report;
		$conf = &$this->conf;

		// set generation time
		$report['timestamp'] = \date_create();

		// Retrieve entries
		// ----------------

		foreach ($conf['breakdown'] as $key => $conf)
		{
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
				->date(':start_date', $conf['from']->format('Y-m-d H:i:s'))
				->date(':end_date', $conf['to']->format('Y-m-d H:i:s'))
				->num(':group', $this->group)
				->run()
				->fetch_all();

			foreach ($sql_totals as &$entry)
			{
				$entry['type'] = \app\AcctgTAccountTypeLib::typefortaccount($entry['taccount']);
				$entry['total'] = \intval(\floatval($entry['total']) * 100) * \app\AcctgTAccountTypeLib::sign($entry['type']) * \app\AcctgTAccountLib::sign($entry['taccount']) / 100;
			}

			$totals[$key] = \app\Arr::gatherkeys($sql_totals, 'taccount', 'total');
		}

		$incometypes = \app\AcctgTAccountTypeLib::inferred_types(\app\AcctgTAccountTypeLib::typebyname('revenue'));
		$expensetypes = \app\AcctgTAccountTypeLib::inferred_types(\app\AcctgTAccountTypeLib::typebyname('expenses'));

		$accepted_types = \array_merge($incometypes, $expensetypes);

		$filtered_totals = [];

		foreach ($totals as $key => $taccounts)
		{
			$filtered_totals[$key] = [];
			foreach ($taccounts as $taccount_id => $total)
			{
				$taccount = \app\AcctgTAccountLib::entry($taccount_id);
				if (\in_array($taccount['type'], $accepted_types))
				{
					$filtered_totals[$key][$taccount_id] = $total;
				}
			}
		}

		$this->report['data'] = $filtered_totals;

		return $this;
	}

	/**
	 * @return int
	 */
	function totals()
	{
		$report = $this->report();

		$incometypes = \app\AcctgTAccountTypeLib::inferred_types(\app\AcctgTAccountTypeLib::typebyname('revenue'));
		$expensetypes = \app\AcctgTAccountTypeLib::inferred_types(\app\AcctgTAccountTypeLib::typebyname('expenses'));

		$totals = [];

		foreach ($report['data'] as $cat => $accts)
		{
			$total_cents = 0;
			foreach ($accts as $taccount_id => $total)
			{
				$taccount = \app\AcctgTAccountLib::entry($taccount_id);
				if (\in_array($taccount['type'], $incometypes))
				{
					$total_cents += \intval($total * 100) * (-1);
				}
				if (\in_array($taccount['type'], $expensetypes))
				{
					$total_cents -= \intval($total * 100);
				}
			}

			$totals[$cat] = $total_cents / 100;
		}

		return $totals;
	}

} # class
