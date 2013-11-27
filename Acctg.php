<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class Acctg
{
	/**
	 * @return string date
	 */
	static function fiscalyear_start_for($reference = null, $group = null)
	{
		#
		# The following uses the US fiscal year.
		#

		// @todo CLEANUP add support for fiscal year of different regions

		if ($reference === null)
		{
			# assume start of financial year for current date is required
			return \date('Y-10-01');
		}
		else # reference provided,
		{
			if (\is_string($reference))
			{
				$reference = \date_create($reference);
			}
			else
			{
				$reference = clone $reference;
			}

			// if the reference is before April, we are technically in a the
			//
			if (\intval($reference->format('m')) < 10)
			{
				$reference = $reference->modify('-1 year');
			}

			return $reference->format('Y-10-01');
		}
	}

} # class
