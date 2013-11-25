<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\Trait_AcctgReportEntry;

class Trait_AcctgReportEntry_Tester
{
	use Trait_AcctgReportEntry;
}

class Trait_AcctgReportEntryTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\trait_exists('\mjolnir\accounting\Trait_AcctgReportEntry'));
	}

	// @todo tests for \mjolnir\accounting\Trait_AcctgReportEntry

} # test
