<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgJournalCollection;

class AcctgJournalCollectionTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgJournalCollection'));
	}

	// @todo tests for \mjolnir\accounting\AcctgJournalCollection

} # test
