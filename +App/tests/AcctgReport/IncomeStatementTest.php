<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgReport_IncomeStatement;

class AcctgReport_IncomeStatementTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgReport_IncomeStatement'));
	}

	// @todo tests for \mjolnir\accounting\AcctgReport_IncomeStatement

} # test
