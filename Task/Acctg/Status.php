<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Task
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class Task_Acctg_Status extends \app\Task_Base
{
	/**
	 * ...
	 */
	function run()
	{
		\app\Task::consolewriter($this->writer);

		$group = $this->get('group', null);

		$group !== false or $group = null;

		$variation = \app\AcctgTAccountLib::checksum($group);

		if ($variation == 0)
		{
			$this->writer->writef(' Accounting equation satisfied. Accounts are balanced.')->eol();
		}
		else # equation not satisfied
		{
			$error = \number_format($variation, 2);
			$this->writer->writef(" Accounting equation not satisfied! Error delta: $error")->eol();
		}
	}

} # class
