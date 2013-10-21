<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class AcctgReportCategory extends \app\AcctgReportEntry
{
	/**
	 * @return static
	 */
	static function instance($title = null)
	{
		$i = parent::instance();
		$i->set('title', $title);
		return $i;
	}

	/**
	 * @return AcctgReportCategoryInterface
	 */
	function newcategory($title)
	{
		$cat = \app\AcctgReportCategory::instance($title);
		$cat->datahandlers_array($this->datahandlers());
		$cat->calculators_array($this->calculators());
		$this->addentry($cat);
		return $cat;
	}

	/**
	 * @return string
	 */
	function render($indent = null)
	{
		$render =
			'
				<tr>
					<td colspan="'.$this->columncount().'" class="'.$this->displayclass().'">
						'.$this->indent($indent, $this->get('title', null)).'
					</td>
				</tr>
			';

		foreach ($this->entries() as $entry)
		{
			$render .= $entry->render($indent + 1);
		}

		if ($this->show_totals)
		{
			$totals = \app\AcctgReportData::instance($this->totals() + ['title' => $this->totalstitle($this->title())]);
			$totals->displayclass_is('acctg-report--totalsrow');
			$totals->calculators_array($this->calculators());
			$totals->datahandlers_array($this->datahandlers());
			$render .= $totals->render($indent);
		}

		return $render;
	}

} # class
