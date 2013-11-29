<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTransactionOperationModel;

class AcctgTransactionOperationModelTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTransactionOperationModel'));
	}

	// @todo tests for \mjolnir\accounting\AcctgTransactionOperationModel

} # test
