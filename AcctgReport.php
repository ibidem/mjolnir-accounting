<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
abstract class AcctgReport extends \app\Instantiatable implements AcctgReportInterface
{
	use \app\Trait_AcctgReport;

	/**
	 * @return static
	 */
	static function instance($options = null, $group = null)
	{
		$i = parent::instance();

		if ($options !== null)
		{
			foreach ($options as $key => $value)
			{
				$i->set($key, $value);
			}
		}

		return $i;
	}

} # class
