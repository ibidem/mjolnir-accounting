<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTransactionModel;

class AcctgTransactionModelTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTransactionModel'));
	}

	// @todo tests for \mjolnir\accounting\AcctgTransactionModel

} # test
