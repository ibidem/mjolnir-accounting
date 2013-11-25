<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgEntity_IncomeStatement;

class AcctgEntity_IncomeStatementTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgEntity_IncomeStatement'));
	}

	// @todo tests for \mjolnir\accounting\AcctgEntity_IncomeStatement

} # test
