<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgEntity_CashFlowStatement;

class AcctgEntity_CashFlowStatementTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgEntity_CashFlowStatement'));
	}

	// @todo tests for \mjolnir\accounting\AcctgEntity_CashFlowStatement

} # test
