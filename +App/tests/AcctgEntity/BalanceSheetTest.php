<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgEntity_BalanceSheet;

class AcctgEntity_BalanceSheetTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgEntity_BalanceSheet'));
	}

	// @todo tests for \mjolnir\accounting\AcctgEntity_BalanceSheet

} # test
