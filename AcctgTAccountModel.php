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

		if (\is_string($input['sign']))
		{
			$input['sign'] = (int) $input['sign'];
		}

		return $input;
	}

} # class
