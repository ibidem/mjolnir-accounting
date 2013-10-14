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
	 * @return int
	 */
	static function namedjournal($slugid)
	{
		return static::find_entry(['slugid' => $slugid])['id'];
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
