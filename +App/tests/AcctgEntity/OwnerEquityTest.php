<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgEntity_OwnerEquity;

class AcctgEntity_OwnerEquityTest extends \app\PHPUnit_Framework_AcctgTestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgEntity_OwnerEquity'));
	}

	/** @test */ function
	basic_usecase()
	{
		$cash = \app\AcctgTAccountLib::named('cash');
		$capital = \app\AcctgTAccountLib::named('common-stock');
		$investments = \app\AcctgTAccountLib::named('investments');
		$withdraws = \app\AcctgTAccountLib::named('draws');
		$marketing = \app\AcctgTAccountLib::named('marketing');
		$revenue = \app\AcctgTAccountLib::named('revenue');

		static::add_transaction
			(
				[
					'journal' => \app\AcctgJournalLib::named('system-ledger'),
					'description' => 'Unit Test',
					'date' => '2012-01-01',
					'operations' => array
					(
						[
							'type' => +1, # debit
							'taccount' => $cash,
							'note' => 'example',
							'amount_value' => 5000,
							'amount_type' => 'USD'
						],
						[
							'type' => -1, # credit
							'taccount' => $capital,
							'note' => 'example',
							'amount_value' => 5000,
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
							'amount_value' => 1000,
							'amount_type' => 'USD'
						],
						[
							'type' => -1, # credit
							'taccount' => $capital,
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
							'amount_value' => 300,
							'amount_type' => 'USD'
						],
						[
							'type' => -1, # credit
							'taccount' => $investments,
							'note' => 'example',
							'amount_value' => 300,
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
							'amount_value' => 800,
							'amount_type' => 'USD'
						],
						[
							'type' => -1, # credit
							'taccount' => $withdraws,
							'note' => 'example',
							'amount_value' => 800,
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

		$oe_statement = \app\AcctgEntity_OwnerEquity::instance
			(
				[
					'breakdown' => array
						(
							'test' => array
								(
									'from' => \date_create('2013-01-01'),
									'to' => \date_create('2015-01-01')
								)
						)
				],
				null
			);

		echo "\n";
		\var_dump($oe_statement->run()->report()['data']['test']); die;
	}

} # test
