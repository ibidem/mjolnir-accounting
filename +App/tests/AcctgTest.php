<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\Acctg;

class AcctgTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\Acctg'));
	}

	// @todo tests for \mjolnir\accounting\Acctg

} # test
