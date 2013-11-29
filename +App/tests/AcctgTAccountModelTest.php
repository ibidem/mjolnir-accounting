<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTAccountModel;

class AcctgTAccountModelTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTAccountModel'));
	}

	// @todo tests for \mjolnir\accounting\AcctgTAccountModel

} # test
