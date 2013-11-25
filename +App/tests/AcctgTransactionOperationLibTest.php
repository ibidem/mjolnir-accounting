<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTransactionOperationLib;

class AcctgTransactionOperationLibTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTransactionOperationLib'));
	}

	// @todo tests for \mjolnir\accounting\AcctgTransactionOperationLib

} # test
