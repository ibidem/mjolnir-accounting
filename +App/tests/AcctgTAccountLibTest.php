<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTAccountLib;

class AcctgTAccountLibTest extends \app\PHPUnit_Framework_AcctgTestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTAccountLib'));
	}

	/** @test */ function
	acctg_zerosum_sign()
	{
		$acct = \app\AcctgTAccountLib::named('salaries');
		$this->assertEquals(+1, \app\AcctgTAccountLib::acctg_zerosum_sign($acct));

		$acct = \app\AcctgTAccountLib::named('cash');
		$this->assertEquals(+1, \app\AcctgTAccountLib::acctg_zerosum_sign($acct));

		$acct = \app\AcctgTAccountLib::named('revenue');
		$this->assertEquals(-1, \app\AcctgTAccountLib::acctg_zerosum_sign($acct));

		$acct = \app\AcctgTAccountLib::named('notes-payable');
		$this->assertEquals(-1, \app\AcctgTAccountLib::acctg_zerosum_sign($acct));
	}

	/** @test */ function
	acct_balance()
	{
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
								'taccount' => \app\AcctgTAccountLib::named('cash'),
								'note' => 'example',
								'amount_value' => 1500,
								'amount_type' => 'USD'
							],
							[
								'type' => -1, # credit
								'taccount' => \app\AcctgTAccountLib::named('revenue'),
								'note' => 'example',
								'amount_value' => 1500,
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
						# the following is intentionally backwards; ie. we're
						# actually adding negatives

						[
							'type' => +1, # debit
							'taccount' => \app\AcctgTAccountLib::named('marketing'),
							'note' => 'example',
							'amount_value' => 1000,
							'amount_type' => 'USD'
						],
						[
							'type' => -1, # credit
							'taccount' => \app\AcctgTAccountLib::named('cash'),
							'note' => 'example',
							'amount_value' => 1000,
							'amount_type' => 'USD'
						],
					),
				]
			);

		$balance = AcctgTAccountLib::acct_balance(\app\AcctgTAccountLib::named('cash'));
		$this->assertEquals(500, $balance);

		$balance = AcctgTAccountLib::acct_balance(\app\AcctgTAccountLib::named('marketing'));
		$this->assertEquals(-1000, $balance);

		$balance = AcctgTAccountLib::acct_balance(\app\AcctgTAccountLib::named('revenue'));
		$this->assertEquals(1500, $balance);

		$this->assertEquals(0, \app\AcctgTAccountLib::checksum());
	}

	/** @test */ function
	acctgeq_balance()
	{
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
							'taccount' => \app\AcctgTAccountLib::named('cash'),
							'note' => 'example',
							'amount_value' => 1500,
							'amount_type' => 'USD'
						],
						[
							'type' => -1, # credit
							'taccount' => \app\AcctgTAccountLib::named('revenue'),
							'note' => 'example',
							'amount_value' => 1500,
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
						# the following is intentionally backwards; ie. we're
						# actually adding negatives

						[
							'type' => -1, # debit
							'taccount' => \app\AcctgTAccountLib::named('marketing'),
							'note' => 'example',
							'amount_value' => 1000,
							'amount_type' => 'USD'
						],
						[
							'type' => +1, # credit
							'taccount' => \app\AcctgTAccountLib::named('cash'),
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
						# the following is intentionally backwards; ie. we're
						# actually adding negatives

						[
							'type' => +1, # debit
							'taccount' => \app\AcctgTAccountLib::named('salaries'),
							'note' => 'example',
							'amount_value' => 300,
							'amount_type' => 'USD'
						],
						[
							'type' => -1, # credit
							'taccount' => \app\AcctgTAccountLib::named('cash'),
							'note' => 'example',
							'amount_value' => 300,
							'amount_type' => 'USD'
						],
					),
				]
			);

		$balance = AcctgTAccountLib::acctgeq_balance(\app\AcctgTAccountLib::named('cash'));
		$this->assertEquals(2200, $balance);

		$balance = AcctgTAccountLib::acctgeq_balance(\app\AcctgTAccountLib::named('marketing'));
		$this->assertEquals(1000, $balance);

		$balance = AcctgTAccountLib::acctgeq_balance(\app\AcctgTAccountLib::named('salaries'));
		$this->assertEquals(-300, $balance);

		$balance = AcctgTAccountLib::acctgeq_balance(\app\AcctgTAccountLib::named('revenue'));
		$this->assertEquals(1500, $balance);

		$this->assertEquals(0, \app\AcctgTAccountLib::checksum());
	}

} # test
