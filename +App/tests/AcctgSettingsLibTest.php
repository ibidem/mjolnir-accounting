<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgSettingsLib;

class AcctgSettingsLibTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgSettingsLib'));
	}

	// @todo tests for \mjolnir\accounting\AcctgSettingsLib

} # test
