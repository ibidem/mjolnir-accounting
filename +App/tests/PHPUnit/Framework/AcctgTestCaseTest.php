<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\PHPUnit_Framework_AcctgTestCase;

class PHPUnit_Framework_AcctgTestCaseTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\PHPUnit_Framework_AcctgTestCase'));
	}

	// @todo tests for \mjolnir\accounting\PHPUnit_Framework_AcctgTestCase

} # test
