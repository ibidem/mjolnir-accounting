<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgEntity_IncomeStatement;

class AcctgEntity_IncomeStatementTest extends \app\PHPUnit_Framework_AcctgTestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgEntity_IncomeStatement'));
	}

	/** @test */ function
	basic_usecase()
	{
		$cash = \app\AcctgTAccountLib::named('cash');
		$marketing = \app\AcctgTAccountLib::named('marketing');
		$revenue = \app\AcctgTAccountLib::named('revenue');

		static::add_transaction
			(
				[
					'journal' => \app\AcctgJournalLib::named('system-ledger'),
					'description' => 'Unit Test',
					'date' => '2013-01-01',
					'operations' => array
					(
						[
							'type' => +1, # debit
							'taccount' => $marketing,
							'note' => 'example',
							'amount_value' => 1000,
							'amount_type' => 'USD'
						],
						[
							'type' => -1, # credit
							'taccount' => $cash,
							'note' => 'example',
							'amount_value' => 1000,
							'amount_type' => 'USD'
						],
					),
				]
			);

		static::add_transaction
			(
				[
					'journal' => \app\AcctgJournalLib::named('system-ledger'),
					'description' => 'Unit Test',
					'date' => '2013-01-01',
					'operations' => array
					(
						[
							'type' => +1, # debit
							'taccount' => $cash,
							'note' => 'example',
							'amount_value' => 1500,
							'amount_type' => 'USD'
						],
						[
							'type' => -1, # credit
							'taccount' => $revenue,
							'note' => 'example',
							'amount_value' => 1500,
							'amount_type' => 'USD'
						],
					),
				]
			);

		$this->assertEquals(0, \app\AcctgTAccountLib::checksum(), 'Acctg equation is not balanced!');

		$income_statement = \app\AcctgEntity_IncomeStatement::instance
			(
				[
					'breakdown' => array
						(
							'total' => array
								(
									'from' => \date_create('2000-01-01'),
									'to' => \date_create('2015-01-01')
								)
						)
				],
				null
			);

		$income_statement->run();

		$this->assertEquals
			(
				[
					'income' => array
						(
							$revenue => +1500
						),
					'expenses' => array
						(
							$marketing => -1000
						),
				],
				$income_statement->report()['data']['total']
			);
	}

} # test
