<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   AcctgEntity
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgEntity_BalanceSheet extends \app\Instantiatable
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
		/** @var AcctgEntity_BalanceSheet $i */
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
					__METHOD__.'account-totals',
					'
						SELECT op.taccount,
							   SUM(op.amount_value * op.type) total

						  FROM `'.\app\AcctgTransactionOperationLib::table().'` op

						  JOIN `'.\app\AcctgTransactionLib::table().'` tr
							ON tr.id = op.transaction

						 WHERE tr.group <=> :group
						   AND unix_timestamp(tr.date) >= unix_timestamp(:start_date)
						   AND unix_timestamp(tr.date) < unix_timestamp(:end_date)

						 GROUP BY op.taccount
					'
				)
				->num(':start_date', $conf['from']->format('Y-m-d H:i:s'))
				->num(':end_date', $conf['to']->format('Y-m-d H:i:s'))
				->num(':group', $this->group)
				->run()
				->fetch_all();

			foreach ($sql_totals as $entry)
			{
				$entry['type'] = \app\AcctgTAccountTypeLib::typefortaccount($entry['taccount']);
				$entry['total'] = $entry['total'] * \app\AcctgTAccountTypeLib::sign($entry['type']) * \app\AcctgTAccountLib::sign($entry['taccount']);
			}

			$totals[$key] = \app\Arr::gatherkeys($sql_totals, 'taccount', 'total');
		}

		$this->report['data'] = $totals;

		return $this;
	}

} # class
