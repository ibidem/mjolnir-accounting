<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Library
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
trait Trait_AcctgReportEntry
{
	use \app\Trait_Renderable;
	use \app\Trait_Meta;

	// ------------------------------------------------------------------------
	// Nested Entries

	/** @var array sub entries */
	protected $nestedentries = null;

	/**
	 * Add sub entry.
	 *
	 * @return static $this
	 */
	function addentry(AcctgReportEntryInterface $entry)
	{
		$this->nestedentries[] = $entry;
		return $this;
	}

	/**
	 * Adds a data entry via raw input. The method will generate a
	 * AcctgReportDataInterface type entry and
	 *
	 * @return AcctgReportDataInterface
	 */
	function newdataentry(array $data)
	{
		$dataentry = \app\AcctgReportData::instance($data);
		$dataentry->datahandlers_array($this->datahandlers());
		$this->addentry($dataentry);
		return $dataentry;
	}

	/**
	 * @return array
	 */
	function entries()
	{
		if ($this->nestedentries === null)
		{
			return [];
		}

		return $this->nestedentries;
	}

	// ------------------------------------------------------------------------
	// Data Handlers

	/** @var array of (string, AcctgReportData) callbacks */
	protected $datahandlers = null;

	/**
	 * @return array
	 */
	function &datahandlers()
	{
		return $this->datahandlers;
	}

	/**
	 * @return array
	 */
	function datahandlers_array(array $datahandlers = null)
	{
		$this->datahandlers = $datahandlers;
		return $this;
	}

	/**
	 * @return array
	 */
	function appendhandler($key, $handler)
	{
		if (\is_callable($handler))
		{
			$this->datahandlers[$key] = $handler;
		}
		else # standard type
		{
			switch ($handler)
			{
				case 'string':
					$this->datahandlers[$key] = function ($key, AcctgReportDataInterface $dataentry)
						{
							return '<td>'.$dataentry->attr($key).'</td>';
						};
					break;

				case 'currency':
					$this->datahandlers[$key] = function ($key, AcctgReportDataInterface $dataentry)
						{
							return '<td class="acctg-report--currency">'.\number_format($dataentry->attr($key), 2).'</td>';
						};
					break;

				default:
					throw new \app\Exception('The data handler type ['.$handler.'] is not supported.');
			}
		}
	}

	// ------------------------------------------------------------------------
	// Calculators

	/** @var array of (string, AcctgReportData) callbacks */
	protected $calculators = null;

	/**
	 * @return array
	 */
	function &datahandlers()
	{
		return $this->calculators;
	}

	/**
	 * @return array
	 */
	function calculators_array(array $calculators = null)
	{
		$this->calculators = $calculators;
		return $this;
	}

	/**
	 * @return array
	 */
	function addcalculator($key, $handler)
	{
		if (\is_callable($handler))
		{
			$this->calculators[$key] = $handler;
		}
		else # standard type
		{
			switch ($handler)
			{
				case 'currency':
					$this->calculators[$key] = function ($key, AcctgReportDataInterface $dataentry)
						{
							// @todo
						};
					break;

				default:
					throw new \app\Exception('The data calculator type ['.$handler.'] is not supported.');
			}
		}
	}

	// ------------------------------------------------------------------------
	// Helpers

	/**
	 * @return string
	 */
	function indent($indent, $text)
	{
		$indent !== null or $indent = 0;
		return \str_repeat(' &nbsp; &nbsp; ', $indent).$text;
	}

} # trait
