<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTAccountLockLib;

class AcctgTAccountLockLibTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTAccountLockLib'));
	}

	// @todo tests for \mjolnir\accounting\AcctgTAccountLockLib

} # test
