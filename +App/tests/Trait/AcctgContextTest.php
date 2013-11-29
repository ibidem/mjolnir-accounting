<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\Trait_AcctgContext;

class Trait_AcctgContext_Tester
{
	use Trait_AcctgContext;
}

class Trait_AcctgContextTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\trait_exists('\mjolnir\accounting\Trait_AcctgContext'));
	}

	// @todo tests for \mjolnir\accounting\Trait_AcctgContext

} # test
