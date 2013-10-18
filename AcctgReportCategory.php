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
		$cat->datahandlers_array($this->datahandlers);
		$this->addentry($cat);
		return $cat;
	}

	/**
	 * @return int
	 */
	protected function columncount()
	{
		return \count($this->datahandlers()) + 1;
	}

	/**
	 * @return string
	 */
	function render($indent = null)
	{
		$render = '<tr><td colspan="'.$this->columncount().'">'.$this->indent($indent, $this->get('title', null)).'</td></tr>';
		foreach ($this->entries() as $entry)
		{
			$render .= $entry->render($indent + 1);
		}

		return $render;
	}

} # class
