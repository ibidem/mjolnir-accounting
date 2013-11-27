<?php namespace mjolnir\accounting\tests;

use \mjolnir\accounting\Acctg;

class AcctgTest extends \app\PHPUnit_Framework_TestCase
{
	/** @test */ function
	can_be_loaded()
	{
		$this->assertTrue(\class_exists('\mjolnir\accounting\Acctg'));
	}

	/** @test */ function
	fiscalyear_start_for()
	{
		$this->assertEquals(\date('Y-10-01'), Acctg::fiscalyear_start_for(null));
		$this->assertEquals(\date('2012-10-01'), Acctg::fiscalyear_start_for('2013-01-01'));

		// mutation test
		$date_original = '2013-01-01';
		$date = \date_create('2013-01-01');
		Acctg::fiscalyear_start_for($date);
		$this->assertEquals($date_original, $date->format('Y-m-d'));
	}

} # test
