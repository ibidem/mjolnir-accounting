<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgReport_OwnerEquity;

class AcctgReport_OwnerEquityTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgReport_OwnerEquity'));
	}

	// @todo tests for \mjolnir\accounting\AcctgReport_OwnerEquity

} # test
