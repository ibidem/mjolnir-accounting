<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTransactionLockLib;

class AcctgTransactionLockLibTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTransactionLockLib'));
	}

	// @todo tests for \mjolnir\accounting\AcctgTransactionLockLib

} # test
