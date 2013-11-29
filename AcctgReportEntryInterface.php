<?php namespace mjolnir\accounting;

use \mjolnir\types\Renderable as Renderable;
use \mjolnir\types\Meta as Meta;

/**
 * @package    mjolnir
 * @category   Accounting
 * @author     Ibidem Team
 * @copyright  (c) 2013 Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
interface AcctgReportEntryInterface extends Renderable, Meta
{
	/**
	 * @return string
	 */
	function render($indent = null);

	/**
	 * @return string
	 */
	function title();

	/**
	 * @return static $this
	 */
	function totalstitle_is($title);

	/**
	 * @return static $this
	 */
	function totalstitle($title);

	/**
	 * @return static $this
	 */
	function displayclass_is($class);

	/**
	 * @return string
	 */
	function displayclass();

	/**
	 * Add sub entry.
	 *
	 * @return static $this
	 */
	function addentry(AcctgReportEntryInterface $entry);

	/**
	 * Adds a data entry via raw input. The method will generate a
	 * AcctgReportDataInterface type entry and
	 *
	 * @return AcctgReportDataInterface
	 */
	function newdataentry(array $data);

	/**
	 * @return array
	 */
	function &datahandlers();

	/**
	 * @return array
	 */
	function datahandlers_array(array $datahandlers = null);

	/**
	 * @return array
	 */
	function appendhandler($key, $handler);

	/**
	 * @return array
	 */
	function &calculators();

	/**
	 * @return array
	 */
	function calculators_array(array $calculators = null);

	/**
	 * @return array
	 */
	function addcalculator($key, $handler);

	/**
	 * @return array
	 */
	function totals();

	/**
	 * @return AcctgReportEntryInterface[]
	 */
	function entries();

} # interface
