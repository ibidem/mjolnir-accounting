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

	/** @var array */
	protected $report = null;

	/**
	 * @return static
	 */
	static function instance(array $conf = null, $group = null)
	{
		/** @var AcctgEntity_IncomeStatement $i */
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
				$entry['total']
					= \intval(\floatval($entry['total']) * 100)
					* \app\AcctgTAccountLib::treesign($entry['taccount'])
					* ( \app\AcctgTAccountTypeLib::is_equity_acct($entry['taccount']) ? -1 : +1 )
					/ 100;
			}

			$totals[$key] = \app\Arr::gatherkeys($sql_totals, 'taccount', 'total');
		}

		$incometypes = \app\AcctgTAccountTypeLib::relatedtypes(\app\AcctgTAccountTypeLib::named('revenue'));
		$expensetypes = \app\AcctgTAccountTypeLib::relatedtypes(\app\AcctgTAccountTypeLib::named('expenses'));

		$filtered_totals = [];

		foreach ($totals as $key => $taccounts)
		{
			$filtered_totals[$key] = [ 'income' => [], 'expenses' => [] ];
			foreach ($taccounts as $taccount_id => $total)
			{
				$taccount = \app\AcctgTAccountLib::entry($taccount_id);
				if (\in_array($taccount['type'], $incometypes))
				{
					$filtered_totals[$key]['income'][$taccount_id] = $total;
				}
				else if (\in_array($taccount['type'], $expensetypes))
				{
					$filtered_totals[$key]['expenses'][$taccount_id] = $total;
				}
			}
		}

		$this->report['data'] = $filtered_totals;

		return $this;
	}

	/**
	 * @return array
	 */
	function totals()
	{
		$report = $this->report();

		$totals = [];

		foreach ($report['data'] as $cat => $data)
		{
			$total_cents = 0;
			foreach ($data['income'] as $total)
			{
				$total_cents += \intval($total * 100);
			}
			foreach ($data['expenses'] as $total)
			{
				$total_cents += \intval($total * 100);
			}

			$totals[$cat] = $total_cents / 100;
		}

		return $totals;
	}

	/**
	 * @return float
	 */
	function total()
	{
		$totals = $this->totals();

		$result = 0;
		foreach ($totals as $total)
		{
			$result += \intval($total * 100);
		}

		return $result / 100;
	}

} # class
