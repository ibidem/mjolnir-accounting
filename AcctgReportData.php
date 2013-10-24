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
		$rowdata = '<td>'.$this->indent($indent, $this->get('title', null)).'</td>';

		$datahandlers = $this->datahandlers();
		if ($datahandlers !== null)
		{
			foreach ($datahandlers as $key => $func)
			{
				$rowdata .= $func($key, $this);
			}
		}

		$entries = $this->entries();

		$subrows = '';
		if (\count($entries) > 0)
		{
			$this->displayclass_is('acctg-report--multirow');

			foreach ($entries as $entry)
			{
				$subrows .= $entry->render($indent + 1);
			}

			$totals = \app\AcctgReportData::instance($this->totals() + ['title' => $this->totalstitle($this->title())]);
			$totals->displayclass_is('acctg-report--totalsrow');
			$totals->calculators_array($this->calculators());
			$totals->datahandlers_array($this->datahandlers());
			$subrows .= $totals->render($indent);

			return
				'
					<tr class="'.$this->displayclass().'">
						<td colspan="'.$this->columncount().'">'.$this->indent($indent, $this->get('title', null)).'</td>
					</tr>
					'.$subrows.'
				';
		}
		else # no subrows
		{
			return
				'
					<tr class="'.$this->displayclass().'">
						'.$rowdata.'
					</tr>
				';
		}
	}

} # class
