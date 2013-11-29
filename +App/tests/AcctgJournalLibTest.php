<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgJournalLib;

class AcctgJournalLibTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgJournalLib'));
	}

	// @todo tests for \mjolnir\accounting\AcctgJournalLib

} # test
