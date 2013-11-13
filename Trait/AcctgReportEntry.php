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

	/**
	 * @return string
	 */
	function title()
	{
		return $this->get('title', null);
	}

	/** @var string|callback */
	protected $totalstitle = null;

	/**
	 * @return static $this
	 */
	function totalstitle_is($title)
	{
		$this->totalstitle = $title;
		return $this;
	}

	/**
	 * @return static $this
	 */
	function totalstitle($title)
	{
		if (\is_callable($this->totalstitle))
		{
			$callback = $this->totalstitle;
			return $callback($title);
		}
		else if ($this->totalstitle !== null)
		{
			return $this->totalstitle;
		}
		else # totalstitle === null
		{
			return '<b>Total</b> '.$title;
		}
	}

	/**
	 * @return int
	 */
	protected function columncount()
	{
		return \count($this->datahandlers()) + 1;
	}

	// ------------------------------------------------------------------------
	// Display Class

	/** @var string entry display class */
	protected $displayclass = null;

	/**
	 * @return static $this
	 */
	function displayclass_is($class)
	{
		$this->displayclass = $class;
		return $this;
	}

	/**
	 * @return string
	 */
	function displayclass()
	{
		if ($this->displayclass === null)
		{
			return 'acctg-report--row';
		}
		else # special class
		{
			return $this->displayclass;
		}
	}

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
		$dataentry->calculators_array($this->calculators());
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
							$render = '<td class="acctg-report--currency">';
							if ($dataentry->attr($key) < 0)
							{
								$render .= '<span class="acctg-negative-number">('.\number_format(-1 * $dataentry->attr($key), 2).')</span>';
							}
							else # positive value or 0, we dont use the -0- convention
							{
								$render .= \number_format($dataentry->attr($key), 2);
							}

							$render .= '</td>';
							return $render;
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
	function &calculators()
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
							// convert to cents
							$total = \intval($dataentry->attr($key) * 100);

							foreach ($dataentry->entries() as $subdataentry)
							{
								// perform add in cents
								$total += \intval($subdataentry->calculate($key) * 100);
							}

							// convert from cents back to expanded version
							return $total / 100;
						};
					break;

				default:
					throw new \app\Exception('The data calculator type ['.$handler.'] is not supported.');
			}
		}
	}

	/**
	 * @return array
	 */
	function totals()
	{
		$totals = [];

		foreach (\array_keys($this->calculators()) as $key)
		{
			$totals[$key] = 0;
		}

		foreach ($this->entries() as $entry)
		{
			$entrytotals = $entry->totals();
			// perform total in cents
			foreach ($entrytotals as $key => $value)
			{
				$totals[$key] += \intval($value * 100);
			}
		}

		// convert back from cents value
		foreach ($totals as $key => $value)
		{
			$totals[$key] = $value / 100;
		}

		return $totals;
	}

	/** @var boolean show totals for category */
	protected $show_totals = true;

	/**
	 * @return static $this
	 */
	function nototals()
	{
		$this->show_totals = false;
		return $this;
	}

	// ------------------------------------------------------------------------
	// Helpers

	/**
	 * @return string
	 */
	protected function indent($indent, $text)
	{
		$indent !== null or $indent = 0;
		return \str_repeat(' &nbsp; &nbsp; ', $indent).$text;
	}

} # trait
