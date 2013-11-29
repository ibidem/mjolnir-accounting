<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgReport;

class AcctgReportTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgReport'));
	}

	// @todo tests for \mjolnir\accounting\AcctgReport

} # test
