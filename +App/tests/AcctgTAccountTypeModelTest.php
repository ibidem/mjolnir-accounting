<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\AcctgTAccountTypeModel;

class AcctgTAccountTypeModelTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\AcctgTAccountTypeModel'));
	}

	// @todo tests for \mjolnir\accounting\AcctgTAccountTypeModel

} # test
