<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgReport_CashFlowStatement;

class AcctgReport_CashFlowStatementTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgReport_CashFlowStatement'));
	}

	// @todo tests for \mjolnir\accounting\AcctgReport_CashFlowStatement

} # test
