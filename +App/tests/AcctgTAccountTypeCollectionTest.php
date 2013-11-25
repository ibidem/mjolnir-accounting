<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTAccountTypeCollection;

class AcctgTAccountTypeCollectionTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTAccountTypeCollection'));
	}

	// @todo tests for \mjolnir\accounting\AcctgTAccountTypeCollection

} # test
