<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTransactionLib;

class AcctgTransactionLibTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTransactionLib'));
	}

	// @todo tests for \mjolnir\accounting\AcctgTransactionLib

} # test
