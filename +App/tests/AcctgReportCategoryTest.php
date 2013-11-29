<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgReportCategory;

class AcctgReportCategoryTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgReportCategory'));
	}

	// @todo tests for \mjolnir\accounting\AcctgReportCategory

} # test
