<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgReport_BalanceSheet;

class AcctgReport_BalanceSheetTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgReport_BalanceSheet'));
	}

	// @todo tests for \mjolnir\accounting\AcctgReport_BalanceSheet

} # test
