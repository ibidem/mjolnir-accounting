<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgJournalLib
{
	use \app\Trait_MarionetteLib;

	/**
	 * ...
	 */
	static function update_process($id, array $input)
	{
		$fieldlist = static::fieldlist();

		$input = \app\Arr::merge(static::entry($id), $input);

		static::updater
			(
				$id, $input,
				$fieldlist['strs'],
				$fieldlist['bools'],
				$fieldlist['nums']
			)
			->run();

		static::clear_cache();
	}

	/**
	 * @return int journal id
	 * @throws \app\Exception if no journal is found
	 */
	static function named($slugid)
	{
		$entry = static::find_entry(['slugid' => $slugid]);

		if ($entry === null)
		{
			throw new \app\Exception("Could not find Journal called [$slugid]");
		}
		else # found journal
		{
			return $entry['id'];
		}
	}

	/**
	 * ...
	 */
	static function install(\mjolnir\types\SQLDatabase $db)
	{
		\app\AcctgJournalLib::push
			(
				[
					'title' => 'General Ledger',
					'slugid' => 'system-ledger',
					'protected' => true,
					'user' => null,
				]
			);
	}

} # class
