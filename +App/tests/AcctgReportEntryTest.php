<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgReportEntry;

class AcctgReportEntryTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgReportEntry'));
	}

	// @todo tests for \mjolnir\accounting\AcctgReportEntry

} # test
