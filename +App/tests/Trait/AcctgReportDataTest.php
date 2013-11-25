<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\Trait_AcctgReportData;

class Trait_AcctgReportData_Tester
{
	use Trait_AcctgReportData;
}

class Trait_AcctgReportDataTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\trait_exists('\mjolnir\accounting\Trait_AcctgReportData'));
	}

	// @todo tests for \mjolnir\accounting\Trait_AcctgReportData

} # test
