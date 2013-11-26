<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\Trait_Model_AcctgCommonLib;

class Trait_Model_AcctgCommonLib_Tester
{
	use Trait_Model_AcctgCommonLib;
}

class Trait_Model_AcctgCommonLibTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\trait_exists('\mjolnir\accounting\Trait_Model_AcctgCommonLib'));
	}

	// @todo tests for \mjolnir\accounting\Trait_Model_AcctgCommonLib

} # test
