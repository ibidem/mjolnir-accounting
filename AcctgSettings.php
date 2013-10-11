<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgSettings extends \app\Instantiatable
					implements \mjolnir\types\Meta
{
	use \app\Trait_Meta;

	/**
	 * @return static
	 */
	static function instance()
	{
		$i = parent::instance();

		$account_settings = \app\Arr::gatherkeys
			(
				\app\AcctgSettingsAccountsLib::entries(null, null),
				'key',
				'account'
			);

		$i->metadata_is
			(
				$account_settings
			);

		return $i;
	}

} # class
