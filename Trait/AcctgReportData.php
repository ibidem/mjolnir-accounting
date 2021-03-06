<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Library
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
trait Trait_AcctgReportData
{
	/**
	 * @return static
	 */
	static function instance(array $data = null)
	{
		$i = parent::instance();

		if ($data !== null)
		{
			$i->set('title', $data['title']);
			$i->set('data', $data);
		}
		else # data === null
		{
			throw new \app\Exception('Data required.');
		}

		return $i;
	}

	/**
	 * @return mixed
	 */
	function attr($key, $default = null)
	{
		$data = $this->get('data', []);
		if (isset($data[$key]))
		{
			return $data[$key];
		}
		else # key not set
		{
			return $default;
		}
	}

	// ------------------------------------------------------------------------
	// Calculators

	/**
	 * @return mixed
	 */
	function calculate($key)
	{
		return $this->calculators[$key]($key, $this);
	}

	/**
	 * @return array
	 */
	function totals()
	{
		$totals = [];
		$calculators = \array_keys($this->calculators());

		if ($calculators !== null)
		{
			foreach ($calculators as $key)
			{
				$totals[$key] = $this->calculate($key);
			}
		}

		return $totals;
	}

} # trait
