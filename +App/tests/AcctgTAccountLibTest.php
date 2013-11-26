<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTAccountLib;

class AcctgTAccountLibTest extends \app\PHPUnit_Framework_AcctgTestCase
{
	/** @test */ function
	checksum()
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
							'amount_value' => 150,
							'amount_type' => 'USD'
						],
						[
							'type' => -1, # credit
							'taccount' => \app\AcctgTAccountLib::named('accts-payable'),
							'note' => 'example',
							'amount_value' => 150,
							'amount_type' => 'USD'
						],
					),
				]
			);

		$this->assertEquals(0, \app\AcctgTAccountLib::checksum(), 'Acctg equation is not balanced!');

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
							'amount_value' => 150,
							'amount_type' => 'USD'
						],
						[
							'type' => +1, # credit
							'taccount' => \app\AcctgTAccountLib::named('accts-payable'),
							'note' => 'example',
							'amount_value' => 150,
							'amount_type' => 'USD'
						],
					),
				]
			);

		$this->assertEquals(300, \app\AcctgTAccountLib::checksum(), 'Expected error in acctg equation balance');
	}

	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTAccountLib'));
	}

	/** @test */ function
	acctg_zerosum_sign()
	{
		$acct = \app\AcctgTAccountLib::named('salaries'); // expenses acct
		$this->assertEquals(-1, \app\AcctgTAccountLib::acctg_zerosum_sign($acct), 'salaries zerosum sign');

		$acct = \app\AcctgTAccountLib::named('cash');
		$this->assertEquals(+1, \app\AcctgTAccountLib::acctg_zerosum_sign($acct), 'cash zerosum sign');

		$acct = \app\AcctgTAccountLib::named('revenue');
		$this->assertEquals(+1, \app\AcctgTAccountLib::acctg_zerosum_sign($acct), 'revenue zerosum sign');

		$acct = \app\AcctgTAccountLib::named('notes-payable'); // liabilities acct
		$this->assertEquals(+1, \app\AcctgTAccountLib::acctg_zerosum_sign($acct), 'notes-payable zerosum sign');
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

		$balance = AcctgTAccountLib::acct_balance(\app\AcctgTAccountLib::named('cash'));
		$this->assertEquals(1500, $balance, 'cash balance');

		$balance = AcctgTAccountLib::acct_balance(\app\AcctgTAccountLib::named('revenue'));
		$this->assertEquals(1500, $balance, 'revenue balance');

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
							'type' => -1, # debit
							'taccount' => \app\AcctgTAccountLib::named('cash'),
							'note' => 'example',
							'amount_value' => 1000,
							'amount_type' => 'USD'
						],
					),
				]
			);

		$balance = AcctgTAccountLib::acct_balance(\app\AcctgTAccountLib::named('marketing'));
		$this->assertEquals(1000, $balance, 'marketing balance');

		$balance = AcctgTAccountLib::acct_balance(\app\AcctgTAccountLib::named('cash'));
		$this->assertEquals(500, $balance, 'cash balance');

		$this->assertEquals(0, \app\AcctgTAccountLib::checksum(), 'Acctg equation is not balanced!');
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

		$balance = AcctgTAccountLib::acctgeq_balance(\app\AcctgTAccountLib::named('cash'));
		$this->assertEquals(1500, $balance, 'cash eq balance');

		$balance = AcctgTAccountLib::acctgeq_balance(\app\AcctgTAccountLib::named('revenue'));
		$this->assertEquals(1500, $balance, 'revenue eq balance');

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

		$balance = AcctgTAccountLib::acctgeq_balance(\app\AcctgTAccountLib::named('marketing'));
		$this->assertEquals(-1000, $balance, 'marketing eq balance');

		$balance = AcctgTAccountLib::acctgeq_balance(\app\AcctgTAccountLib::named('cash'));
		$this->assertEquals(500, $balance, 'cash eq balance');

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
							'type' => -1, # credit
							'taccount' => \app\AcctgTAccountLib::named('marketing'),
							'note' => 'example',
							'amount_value' => 200,
							'amount_type' => 'USD'
						],
						[
							'type' => +1, # debit
							'taccount' => \app\AcctgTAccountLib::named('cash'),
							'note' => 'example',
							'amount_value' => 200,
							'amount_type' => 'USD'
						],
					),
				]
			);

		$balance = AcctgTAccountLib::acctgeq_balance(\app\AcctgTAccountLib::named('marketing'));
		$this->assertEquals(-800, $balance, 'marketing eq balance');

		$balance = AcctgTAccountLib::acctgeq_balance(\app\AcctgTAccountLib::named('cash'));
		$this->assertEquals(700, $balance, 'cash eq balance');

		$this->assertEquals(0, \app\AcctgTAccountLib::checksum(), 'Acctg equation is not balanced!');
	}

	/** @test */ function
	acctg_zerosum_balance()
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
						[
							'type' => -1, # credit
							'taccount' => \app\AcctgTAccountLib::named('cash'),
							'note' => 'example',
							'amount_value' => 350,
							'amount_type' => 'USD'
						],
						[
							'type' => +1, # debit
							'taccount' => \app\AcctgTAccountLib::named('marketing'),
							'note' => 'example',
							'amount_value' => 350,
							'amount_type' => 'USD'
						],
					),
				]
			);

		$balance = AcctgTAccountLib::acctg_zerosum_balance(\app\AcctgTAccountLib::named('cash'));
		$this->assertEquals(1150, $balance, 'cash zerosum balance');

		$balance = AcctgTAccountLib::acctg_zerosum_balance(\app\AcctgTAccountLib::named('revenue'));
		$this->assertEquals(-1500, $balance, 'revenue zerosum balance');

		$balance = AcctgTAccountLib::acctg_zerosum_balance(\app\AcctgTAccountLib::named('marketing'));
		$this->assertEquals(350, $balance, 'marketing zerosum balance');

		$this->assertEquals(0, \app\AcctgTAccountLib::checksum(), 'Acctg equation is not balanced!');
	}

} # test
