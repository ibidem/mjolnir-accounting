<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\Exception_AcctgError;

class Exception_AcctgErrorTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\Exception_AcctgError'));
	}

	// @todo tests for \mjolnir\accounting\Exception_AcctgError

} # test
