<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\Task_Acctg_Status;

class Task_Acctg_StatusTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\Task_Acctg_Status'));
	}

	// @todo tests for \mjolnir\accounting\Task_Acctg_Status

} # test
