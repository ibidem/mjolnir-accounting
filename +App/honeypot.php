<?php namespace app;

// This is an IDE honeypot. It tells IDEs the class hirarchy, but otherwise has
// no effect on your application. :)

// HowTo: order honeypot -n 'mjolnir\accounting'


/**
 * @method \app\Validator auditor()
 * @method \app\AcctgJournalCollection put(array $collection)
 * @method \app\AcctgJournalCollection delete()
 * @method \app\AcctgJournalCollection registerdriver($driver_id, $driver)
 * @method \app\MarionetteDriver getdriver($field, $driver_id, $driverconfig)
 * @method \app\MarionetteCollection collection()
 * @method \app\MarionetteModel model()
 */
class AcctgJournalCollection extends \mjolnir\accounting\AcctgJournalCollection
{
	/** @return \app\AcctgJournalCollection */
	static function instance($db = null) { return parent::instance($db); }
}

class AcctgJournalLib extends \mjolnir\accounting\AcctgJournalLib
{
	/** @return \app\Validator */
	static function update_check($id, array $fields) { return parent::update_check($id, $fields); }
	/** @return \app\SQLStatement */
	static function statement($identifier, $sql, $lang = null) { return parent::statement($identifier, $sql, $lang); }
}

/**
 * @method \app\Validator auditor()
 * @method \app\AcctgJournalModel do_patch($id, array $entry)
 * @method \app\AcctgJournalModel delete($id)
 * @method \app\AcctgJournalModel registerdriver($driver_id, $driver)
 * @method \app\MarionetteDriver getdriver($field, $driver_id, $driverconfig)
 * @method \app\MarionetteModel collection()
 * @method \app\MarionetteModel model()
 */
class AcctgJournalModel extends \mjolnir\accounting\AcctgJournalModel
{
	/** @return \app\AcctgJournalModel */
	static function instance($db = null) { return parent::instance($db); }
}

/**
 * @method \app\Validator auditor()
 * @method \app\AcctgTAccountCollection put(array $collection)
 * @method \app\AcctgTAccountCollection delete()
 * @method \app\AcctgTAccountCollection registerdriver($driver_id, $driver)
 * @method \app\MarionetteDriver getdriver($field, $driver_id, $driverconfig)
 * @method \app\MarionetteCollection collection()
 * @method \app\MarionetteModel model()
 */
class AcctgTAccountCollection extends \mjolnir\accounting\AcctgTAccountCollection
{
	/** @return \app\AcctgTAccountCollection */
	static function instance($db = null) { return parent::instance($db); }
}

class AcctgTAccountLib extends \mjolnir\accounting\AcctgTAccountLib
{
	/** @return \app\Validator */
	static function update_check($id, array $fields) { return parent::update_check($id, $fields); }
	/** @return \app\SQLStatement */
	static function statement($identifier, $sql, $lang = null) { return parent::statement($identifier, $sql, $lang); }
}

/**
 * @method \app\Validator auditor()
 * @method \app\AcctgTAccountModel do_patch($id, array $entry)
 * @method \app\AcctgTAccountModel delete($id)
 * @method \app\AcctgTAccountModel registerdriver($driver_id, $driver)
 * @method \app\MarionetteDriver getdriver($field, $driver_id, $driverconfig)
 * @method \app\MarionetteModel collection()
 * @method \app\MarionetteModel model()
 */
class AcctgTAccountModel extends \mjolnir\accounting\AcctgTAccountModel
{
	/** @return \app\AcctgTAccountModel */
	static function instance($db = null) { return parent::instance($db); }
}

/**
 * @method \app\Validator auditor()
 * @method \app\AcctgTransactionCollection put(array $collection)
 * @method \app\AcctgTransactionCollection delete()
 * @method \app\AcctgTransactionCollection registerdriver($driver_id, $driver)
 * @method \app\MarionetteDriver getdriver($field, $driver_id, $driverconfig)
 * @method \app\MarionetteCollection collection()
 * @method \app\MarionetteModel model()
 */
class AcctgTransactionCollection extends \mjolnir\accounting\AcctgTransactionCollection
{
	/** @return \app\AcctgTransactionCollection */
	static function instance($db = null) { return parent::instance($db); }
}

class AcctgTransactionLib extends \mjolnir\accounting\AcctgTransactionLib
{
	/** @return \app\Validator */
	static function update_check($id, array $fields) { return parent::update_check($id, $fields); }
	/** @return \app\SQLStatement */
	static function statement($identifier, $sql, $lang = null) { return parent::statement($identifier, $sql, $lang); }
}

/**
 * @method \app\Validator auditor()
 * @method \app\AcctgTransactionModel do_patch($id, array $entry)
 * @method \app\AcctgTransactionModel delete($id)
 * @method \app\AcctgTransactionModel registerdriver($driver_id, $driver)
 * @method \app\MarionetteDriver getdriver($field, $driver_id, $driverconfig)
 * @method \app\MarionetteModel collection()
 * @method \app\MarionetteModel model()
 */
class AcctgTransactionModel extends \mjolnir\accounting\AcctgTransactionModel
{
	/** @return \app\AcctgTransactionModel */
	static function instance($db = null) { return parent::instance($db); }
}
