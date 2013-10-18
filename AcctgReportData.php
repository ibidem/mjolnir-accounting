<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgReportData extends \app\AcctgReportEntry implements AcctgReportDataInterface
{
	use \app\Trait_AcctgReportData;

	/**
	 * @return string
	 */
	function render($indent = null)
	{
		$render = '<tr>';

		$render .= '<td>'.$this->indent($indent, $this->get('title', null)).'</td>';

		foreach ($this->datahandlers() as $key => $func)
		{
			$render .= $func($key, $this);
		}

		$render .= '</tr>';

		foreach ($this->entres() as $entry)
		{
			$render .= $entry->render($indent + 1);
		}

		$totals = \app\AcctgReportData::instance($this->totals() + ['title' => 'Total '.$this->title()]);
		$totals->calculators_array($this->calculators());
		$render .= $totals->render($indent);

		return $render;
	}

} # class
