<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgSettings;

class AcctgSettingsTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgSettings'));
	}

	// @todo tests for \mjolnir\accounting\AcctgSettings

} # test
