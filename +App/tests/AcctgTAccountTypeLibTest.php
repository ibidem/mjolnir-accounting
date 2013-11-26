<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTAccountTypeLib;

class AcctgTAccountTypeLibTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTAccountTypeLib'));
	}

	/** @test */ function
	typeslugs_for()
	{
		$this->assertEquals(['equity', 'expenses', 'general-expenses'], AcctgTAccountTypeLib::typeslugs_for(\app\AcctgTAccountLib::named('marketing')));
	}

	/** @test */ function
	typeids_for()
	{
		$this->assertEquals
			(
				[
					\app\AcctgTAccountTypeLib::named('equity'),
					\app\AcctgTAccountTypeLib::named('expenses'),
					\app\AcctgTAccountTypeLib::named('general-expenses'),
				],
				AcctgTAccountTypeLib::typeids_for(\app\AcctgTAccountLib::named('marketing')));
	}

	/** @test */ function
	is_equity_acct()
	{
		$this->assertTrue(AcctgTAccountTypeLib::is_equity_acct(\app\AcctgTAccountLib::named('marketing')));
		$this->assertTrue(AcctgTAccountTypeLib::is_equity_acct(\app\AcctgTAccountLib::named('accts-payable')));
		$this->assertFalse(AcctgTAccountTypeLib::is_equity_acct(\app\AcctgTAccountLib::named('inventory')));
	}

} # test
