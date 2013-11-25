<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTAccountCollection;

class AcctgTAccountCollectionTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTAccountCollection'));
	}

	// @todo tests for \mjolnir\accounting\AcctgTAccountCollection

} # test
