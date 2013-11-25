<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTAccountTypeLib;

class AcctgTAccountTypeLibTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTAccountTypeLib'));
	}

	// @todo tests for \mjolnir\accounting\AcctgTAccountTypeLib

} # test
