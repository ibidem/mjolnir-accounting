<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgProcedure extends \app\Instantiatable implements \mjolnir\types\Meta
{
	use \app\Trait_Meta;

	/** @var mixed */
	protected $context = null;

	/**
	 * @return static
	 */
	static function instance($context = null)
	{
		$i = parent::instance();
		$i->metadata_is(\app\CFS::config('mjolnir/acctg/procedures')[static::driverkey()]);
		$i->context = $context;
		return $i;
	}

	/**
	 * @return string
	 */
	static function driverkey()
	{
		return static::$driverkey;
	}

	/**
	 * @return mjolnir\types\Renderable
	 */
	function view($parentview)
	{
		$driverkey = static::driverkey();
		return \app\View::instance("mjolnir/accounting/procedure/{$driverkey}")
			->inherit($parentview)
			->pass('driver', $this);
	}

} # class
