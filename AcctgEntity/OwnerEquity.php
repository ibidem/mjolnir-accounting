<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   AcctgEntity
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgEntity_OwnerEquity extends \app\Instantiatable
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
		/** @var AcctgEntity_OwnerEquity $i */
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

		foreach ($conf['breakdown'] as &$entry)
		{
			isset($entry['target']) or $entry['target'] = $entry['to'];
		}

		// set generation time
		$report['timestamp'] = \date_create();

		// Retrieve entries
		// ----------------

		$investments_type = \app\AcctgTAccountTypeLib::find_entry(['slugid' => 'investments']);
		$capitalstock_type = \app\AcctgTAccountTypeLib::find_entry(['slugid' => 'capital-stock']);
		$retained_earnings_type = \app\AcctgTAccountTypeLib::find_entry(['slugid' => 'retained-earnings']);

		foreach ($conf['breakdown'] as $key => $conf)
		{
			// retrieve all relevant capital accounts for start of year calculations
			$sql_totals = \app\SQL::prepare
				(
					__METHOD__.':investments-captalstock-retained-earnings-accounts',
					'
						SELECT taccount.id taccount,
							   type.id type,
							   SUM(op.amount_value * op.type) total

						  FROM `'.\app\AcctgTAccountLib::table().'` taccount

						  JOIN `'.\app\AcctgTransactionOperationLib::table().'` op
							ON taccount.id = op.taccount

						  JOIN `'.\app\AcctgTransactionLib::table().'` tr
							ON op.transaction = tr.id

						  JOIN `'.\app\AcctgTaccountTypeLib::table().'` type
							ON type.id = taccount.type

						 WHERE tr.date < :start_of_year
						   AND tr.group <=> :group
						   AND
						   (
								(type.lft >= :capital_stock_lft AND type.rgt <= :capital_stock_rgt)
								OR
								(type.lft >= :investments_lft AND type.rgt <= :investments_rgt)
								OR
								(type.lft >= :retained_earnings_lft AND type.rgt <= :retained_earnings_rgt)
						   )

						 GROUP BY taccount.id
					'
				)
				->num(':capital_stock_lft', $capitalstock_type['lft'])
				->num(':capital_stock_rgt', $capitalstock_type['rgt'])
				->num(':investments_lft', $investments_type['lft'])
				->num(':investments_rgt', $investments_type['rgt'])
				->num(':retained_earnings_lft', $retained_earnings_type['lft'])
				->num(':retained_earnings_rgt', $retained_earnings_type['rgt'])
				->num(':group', $this->group)
				->date(':start_of_year', \app\Acctg::fiscalyear_start_for($conf['target'], $this->group))
				->run()
				->fetch_all();

			// account for value sign
			$start_of_year_capital_cents = 0;
			foreach ($sql_totals as &$entry)
			{
				$entry['types'] = \app\AcctgTAccountTypeLib::alltypesfortaccount($entry['taccount']);
				// we multiply by -1 to adjust Dr/Cr to show positive instead of negative values
				$entry['total'] = $entry['total'] * \app\AcctgTAccountTypeLib::sign($entry['type']) * \app\AcctgTAccountLib::sign($entry['taccount']) * (-1);
				$start_of_year_capital_cents += \intval($entry['total'] * 100);
			}

			// retrieve investment accounts
			$sql_totals = \app\SQL::prepare
				(
					__METHOD__.':investments-accounts-for-the-period',
					'
						SELECT taccount.id taccount,
							   type.id type,
							   SUM(op.amount_value * op.type) total

						  FROM `'.\app\AcctgTAccountLib::table().'` taccount

						  JOIN `'.\app\AcctgTransactionOperationLib::table().'` op
							ON taccount.id = op.taccount

						  JOIN `'.\app\AcctgTransactionLib::table().'` tr
							ON op.transaction = tr.id

						  JOIN `'.\app\AcctgTaccountTypeLib::table().'` type
							ON type.id = taccount.type

						 WHERE tr.date >= :start_of_year
						   AND tr.group <=> :group
						   AND type.lft >= :investments_lft
						   AND type.rgt <= :investments_rgt

						 GROUP BY taccount.id
					'
				)
				->num(':investments_lft', $investments_type['lft'])
				->num(':investments_rgt', $investments_type['rgt'])
				->num(':group', $this->group)
				->date(':start_of_year', \app\Acctg::fiscalyear_start_for($conf['target'], $this->group))
				->run()
				->fetch_all();

			// account for value sign
			$investments_for_period_cents = 0;
			foreach ($sql_totals as &$entry)
			{
				$entry['types'] = \app\AcctgTAccountTypeLib::alltypesfortaccount($entry['taccount']);
				// we multiply by -1 to adjust Dr/Cr to show positive instead of negative values
				$entry['total'] = $entry['total'] * \app\AcctgTAccountTypeLib::sign($entry['type']) * \app\AcctgTAccountLib::sign($entry['taccount']) * (-1);
				$investments_for_period_cents += \intval($entry['total'] * 100);
			}

			$totals[$key] = $start_of_year_capital_cents + $investments_for_period_cents;

			// Calculate Net Income/Loss
			// -------------------------

			$start_of_period = \date_create(\app\Acctg::fiscalyear_start_for($conf['target'], $this->group));
			$end_of_period = clone $start_of_period;
			$end_of_period->modify('+12 months');

			$income_statement = \app\AcctgEntity_IncomeStatement::instance
				(
					[
						'breakdown' => array
							(
								'total' => array
									(
										'from' => $start_of_period,
										'to' => $end_of_period
									),
							),
					],
					$this->group
				);

			$totals[$key] += \intval($income_statement->run()->total() * 100) * (-1);

			// Withdraws
			// ---------

			$draws = \app\AcctgTAccountTypeLib::find_entry(['slugid' => 'withdraws']);

			$sql_totals_draws = \app\SQL::prepare
				(
					__METHOD__,
					'
						SELECT taccount.id taccount,
							   type.id type,
							   SUM(op.amount_value * op.type) total

						  FROM `'.\app\AcctgTAccountLib::table().'` taccount

						  JOIN `'.\app\AcctgTransactionOperationLib::table().'` op
							ON taccount.id = op.taccount

						  JOIN `'.\app\AcctgTransactionLib::table().'` tr
							ON op.transaction = tr.id

						  JOIN `'.\app\AcctgTaccountTypeLib::table().'` type
							ON type.id = taccount.type

						 WHERE tr.date BETWEEN :start_of_period AND :end_of_period
						   AND tr.group <=> :group
						   AND type.lft >= :revenue_lft
						   AND type.rgt <= :revenue_rgt

						 GROUP BY taccount.id
					'
				)
				->num(':revenue_lft', $draws['lft'])
				->num(':revenue_rgt', $draws['rgt'])
				->num(':group', $this->group)
				->date(':start_of_period', $start_of_period->format('Y-m-d H:i:s'))
				->date(':end_of_period', $end_of_period->format('Y-m-d H:i:s'))
				->run()
				->fetch_all();

			foreach ($sql_totals_draws as &$entry)
			{
				$totals[$key] += \intval(\floatval($entry['total']) * 100) * \app\AcctgTAccountTypeLib::sign($entry['type']) * \app\AcctgTAccountLib::sign($entry['taccount']) * (-1);
			}
		}

		foreach ($totals as &$total)
		{
			$total = $total / 100;
		}

		$this->report['data'] = $totals;

		return $this;
	}

} # class
