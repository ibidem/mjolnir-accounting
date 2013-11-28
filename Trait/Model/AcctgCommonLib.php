<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Trait
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
trait Trait_Model_AcctgCommonLib
{
	/**
	 * @return int
	 */
	static function rootsign($entity)
	{
		$entries = static::statement
			(
				__METHOD__,
				'
					SELECT entry.sign
					  FROM `'.static::table().'` entry

					  JOIN `'.static::table().'` target
						ON target.id = :target

				     WHERE entry.lft <= target.lft
					   AND entry.rgt >= target.rgt
				'
			)
			->num(':target', $entity)
			->run()
			->fetch_all();

		try
		{
			return \app\Arr::intmul($entries, 'sign');
		}
		catch (\Exception $e)
		{
			throw new \app\Exception("Failed computing rootsign for [$entity].");
		}
	}

	/**
	 * @return int ID
	 * @throws \app\Exception could not find entity
	 */
	static function named($slugid)
	{
		$entry = static::find_entry(['slugid' => $slugid]);

		if ($entry === null)
		{
			throw new \app\Exception("Could not find TAccount called [$slugid]");
		}
		else # found TAccount
		{
			return $entry['id'];
		}
	}

} # trait
