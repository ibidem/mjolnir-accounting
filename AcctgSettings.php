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
		/** @var AcctgSettings $i */
		$i = parent::instance();

		$account_settings = \app\Arr::gatherkeys
			(
				\app\AcctgSettingsLib::entries(null, null),
				'slugid',
				'taccount'
			);

		$i->metadata_is
			(
				$account_settings
			);

		return $i;
	}

	/**
	 * You may provide a type check in the form of a type slug. If the account
	 * set in the configuration is NOT of the type specified it will be
	 * rejected by throwing an exception.
	 *
	 * @return int account id
	 */
	function acct($key, $typecheck = false)
	{
		$acct = $this->get($key, null);

		if ($acct === null)
		{
			throw new \app\Exception("The account [$key] is not defined in system settings.");
		}

		if ($typecheck !== false)
		{
			// Check for compatibility of type
			// -------------------------------

			$acct_type = \app\AcctgTAccountType::entry(\app\AcctgTAccountLib::entry($acct)['type']);
			$target_type = \app\AcctgTAccountTypeLib::named($typecheck);

			if ( ! ($target_type['lft'] <= $acct_type['lft'] && $target_type['rgt'] >= $acct_type['rgt']))
			{
				$acct_slugid = $acct_type['slugid'];
				throw new \app\Exception("The account [$key] has an account of incompatible type set. Expected [$typecheck] but recieved [$acct_slugid].");
			}
		}

		return $acct;
	}

} # class
