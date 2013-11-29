<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTransactionCollection;

class AcctgTransactionCollectionTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTransactionCollection'));
	}

	// @todo tests for \mjolnir\accounting\AcctgTransactionCollection

} # test
