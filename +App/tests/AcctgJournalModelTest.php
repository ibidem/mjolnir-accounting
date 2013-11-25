<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgJournalModel;

class AcctgJournalModelTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgJournalModel'));
	}

	// @todo tests for \mjolnir\accounting\AcctgJournalModel

} # test
