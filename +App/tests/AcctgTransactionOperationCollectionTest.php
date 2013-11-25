<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTransactionOperationCollection;

class AcctgTransactionOperationCollectionTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTransactionOperationCollection'));
	}

	// @todo tests for \mjolnir\accounting\AcctgTransactionOperationCollection

} # test
