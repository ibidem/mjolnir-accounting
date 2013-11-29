<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\Trait_AcctgReport;

class Trait_AcctgReport_Tester
{
	use Trait_AcctgReport;
}

class Trait_AcctgReportTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\trait_exists('\mjolnir\accounting\Trait_AcctgReport'));
	}

	// @todo tests for \mjolnir\accounting\Trait_AcctgReport

} # test
