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

		foreach ($conf['breakdown'] as $key => $cnf)
		{
			$totals[$key] = [ 'assets' => [], 'liabilities' => [], 'capital' => 0.00 ];

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
				->num(':start_date', $cnf['from']->format('Y-m-d H:i:s'))
				->num(':end_date', $cnf['to']->format('Y-m-d H:i:s'))
				->num(':group', $this->group)
				->run()
				->fetch_all();

			$asset_types = \app\AcctgTAccountTypeLib::relatedtypes(\app\AcctgTAccountTypeLib::named('assets'));
			$liability_types = \app\AcctgTAccountTypeLib::relatedtypes(\app\AcctgTAccountTypeLib::named('liabilities'));

			foreach ($sql_totals as $entry)
			{
				$entry_type = \app\AcctgTAccountLib::entry($entry['taccount'])['type'];
				if (\in_array($entry_type, $asset_types))
				{
					$totals[$key]['assets'][$entry['taccount']] = $entry['total'] * \app\AcctgTAccountLib::treesign($entry['taccount']);
				}
				else if (\in_array($entry_type, $liability_types))
				{
					$totals[$key]['liabilities'][$entry['taccount']] = $entry['total'] * \app\AcctgTAccountLib::treesign($entry['taccount']);
				}
			}

			$oe_statement = \app\AcctgEntity_OwnerEquity::instance
				(
					[
						'breakdown' => array
							(
								'total' => array
									(
										'from' => $cnf['from'],
										'to' => $cnf['to']
									),
							)
					],
					$this->group
				);

			$totals[$key]['capital'] = $oe_statement->run()->report()['data']['total']['ending_capital'];
		}

		$this->report['data'] = $totals;

		return $this;
	}

} # class
