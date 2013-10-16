<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgTAccountModel extends \app\MarionetteModel
{
	/**
	 * @var string
	 */
	static $configfile = 'mjolnir/models/acctg-taccount';

	/**
	 * Normalizing value format, filling in optional components, etc.
	 *
	 * @return array normalized entry
	 */
	function parse(array $input)
	{
		isset($input['sign']) or $input['sign'] = +1;
		isset($input['lft']) or $input['lft'] = null;
		isset($input['rgt']) or $input['rgt'] = null;
		isset($input['group']) or $input['group'] = null;

		if (\is_string($input['sign']))
		{
			$input['sign'] = (int) $input['sign'];
		}

		return parent::parse($input);
	}

	/**
	 * @return \mjolnir\types\Validator
	 */
	function auditor()
	{
		$config = static::config();
		$error_messages = isset($config['errors']) ? $config['errors'] : [];

		return parent::auditor()
			->adderrormessages($error_messages);
	}

} # class
