<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgEntity_OwnerEquity;

class AcctgEntity_OwnerEquityTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgEntity_OwnerEquity'));
	}

	// @todo tests for \mjolnir\accounting\AcctgEntity_OwnerEquity

} # test
