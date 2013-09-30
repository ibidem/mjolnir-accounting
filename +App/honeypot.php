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
 * @method \app\AcctgJournalCollection filter($conditions)
 */
class AcctgJournalCollection extends \mjolnir\accounting\AcctgJournalCollection
{
	/** @return \app\AcctgJournalCollection */
	static function instance($db = null) { return parent::instance($db); }
}

class AcctgJournalLib extends \mjolnir\accounting\AcctgJournalLib
{
	/** @return \app\Validator */
	static function check(array $fields, $context = null) { return parent::check($fields, $context); }
	/** @return \app\MarionetteModel */
	static function marionette_model() { return parent::marionette_model(); }
	/** @return \app\MarionetteCollection */
	static function marionette_collection() { return parent::marionette_collection(); }
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
 * @method \app\AcctgJournalModel filter($conditions)
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
 * @method \app\AcctgTAccountCollection filter($conditions)
 */
class AcctgTAccountCollection extends \mjolnir\accounting\AcctgTAccountCollection
{
	/** @return \app\AcctgTAccountCollection */
	static function instance($db = null) { return parent::instance($db); }
}

class AcctgTAccountLib extends \mjolnir\accounting\AcctgTAccountLib
{
	/** @return \app\Validator */
	static function check(array $fields, $context = null) { return parent::check($fields, $context); }
	/** @return \app\MarionetteModel */
	static function marionette_model() { return parent::marionette_model(); }
	/** @return \app\MarionetteCollection */
	static function marionette_collection() { return parent::marionette_collection(); }
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
 * @method \app\AcctgTAccountModel filter($conditions)
 */
class AcctgTAccountModel extends \mjolnir\accounting\AcctgTAccountModel
{
	/** @return \app\AcctgTAccountModel */
	static function instance($db = null) { return parent::instance($db); }
}

/**
 * @method \app\Validator auditor()
 * @method \app\AcctgTAccountTypeCollection put(array $collection)
 * @method \app\AcctgTAccountTypeCollection delete()
 * @method \app\AcctgTAccountTypeCollection registerdriver($driver_id, $driver)
 * @method \app\MarionetteDriver getdriver($field, $driver_id, $driverconfig)
 * @method \app\MarionetteCollection collection()
 * @method \app\MarionetteModel model()
 * @method \app\AcctgTAccountTypeCollection filter($conditions)
 */
class AcctgTAccountTypeCollection extends \mjolnir\accounting\AcctgTAccountTypeCollection
{
	/** @return \app\AcctgTAccountTypeCollection */
	static function instance($db = null) { return parent::instance($db); }
}

/**
 * @method \app\Validator auditor()
 * @method \app\AcctgTAccountTypeHintCollection put(array $collection)
 * @method \app\AcctgTAccountTypeHintCollection delete()
 * @method \app\AcctgTAccountTypeHintCollection registerdriver($driver_id, $driver)
 * @method \app\MarionetteDriver getdriver($field, $driver_id, $driverconfig)
 * @method \app\MarionetteCollection collection()
 * @method \app\MarionetteModel model()
 * @method \app\AcctgTAccountTypeHintCollection filter($conditions)
 */
class AcctgTAccountTypeHintCollection extends \mjolnir\accounting\AcctgTAccountTypeHintCollection
{
	/** @return \app\AcctgTAccountTypeHintCollection */
	static function instance($db = null) { return parent::instance($db); }
}

class AcctgTAccountTypeHintLib extends \mjolnir\accounting\AcctgTAccountTypeHintLib
{
	/** @return \app\Validator */
	static function check(array $fields, $context = null) { return parent::check($fields, $context); }
	/** @return \app\MarionetteModel */
	static function marionette_model() { return parent::marionette_model(); }
	/** @return \app\MarionetteCollection */
	static function marionette_collection() { return parent::marionette_collection(); }
	/** @return \app\Validator */
	static function update_check($id, array $fields) { return parent::update_check($id, $fields); }
	/** @return \app\SQLStatement */
	static function statement($identifier, $sql, $lang = null) { return parent::statement($identifier, $sql, $lang); }
}

/**
 * @method \app\Validator auditor()
 * @method \app\AcctgTAccountTypeHintModel do_patch($id, array $entry)
 * @method \app\AcctgTAccountTypeHintModel delete($id)
 * @method \app\AcctgTAccountTypeHintModel registerdriver($driver_id, $driver)
 * @method \app\MarionetteDriver getdriver($field, $driver_id, $driverconfig)
 * @method \app\MarionetteModel collection()
 * @method \app\MarionetteModel model()
 * @method \app\AcctgTAccountTypeHintModel filter($conditions)
 */
class AcctgTAccountTypeHintModel extends \mjolnir\accounting\AcctgTAccountTypeHintModel
{
	/** @return \app\AcctgTAccountTypeHintModel */
	static function instance($db = null) { return parent::instance($db); }
}

class AcctgTAccountTypeLib extends \mjolnir\accounting\AcctgTAccountTypeLib
{
	/** @return \app\Validator */
	static function check(array $fields, $context = null) { return parent::check($fields, $context); }
	/** @return \app\MarionetteModel */
	static function marionette_model() { return parent::marionette_model(); }
	/** @return \app\MarionetteCollection */
	static function marionette_collection() { return parent::marionette_collection(); }
	/** @return \app\Validator */
	static function update_check($id, array $fields) { return parent::update_check($id, $fields); }
	/** @return \app\SQLStatement */
	static function statement($identifier, $sql, $lang = null) { return parent::statement($identifier, $sql, $lang); }
}

/**
 * @method \app\Validator auditor()
 * @method \app\AcctgTAccountTypeModel do_patch($id, array $entry)
 * @method \app\AcctgTAccountTypeModel delete($id)
 * @method \app\AcctgTAccountTypeModel registerdriver($driver_id, $driver)
 * @method \app\MarionetteDriver getdriver($field, $driver_id, $driverconfig)
 * @method \app\MarionetteModel collection()
 * @method \app\MarionetteModel model()
 * @method \app\AcctgTAccountTypeModel filter($conditions)
 */
class AcctgTAccountTypeModel extends \mjolnir\accounting\AcctgTAccountTypeModel
{
	/** @return \app\AcctgTAccountTypeModel */
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
 * @method \app\AcctgTransactionCollection filter($conditions)
 */
class AcctgTransactionCollection extends \mjolnir\accounting\AcctgTransactionCollection
{
	/** @return \app\AcctgTransactionCollection */
	static function instance($db = null) { return parent::instance($db); }
}

class AcctgTransactionLib extends \mjolnir\accounting\AcctgTransactionLib
{
	/** @return \app\Validator */
	static function check(array $fields, $context = null) { return parent::check($fields, $context); }
	/** @return \app\MarionetteModel */
	static function marionette_model() { return parent::marionette_model(); }
	/** @return \app\MarionetteCollection */
	static function marionette_collection() { return parent::marionette_collection(); }
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
 * @method \app\AcctgTransactionModel filter($conditions)
 */
class AcctgTransactionModel extends \mjolnir\accounting\AcctgTransactionModel
{
	/** @return \app\AcctgTransactionModel */
	static function instance($db = null) { return parent::instance($db); }
}

/**
 * @method \app\Validator auditor()
 * @method \app\AcctgTransactionOperationCollection put(array $collection)
 * @method \app\AcctgTransactionOperationCollection delete()
 * @method \app\AcctgTransactionOperationCollection registerdriver($driver_id, $driver)
 * @method \app\MarionetteDriver getdriver($field, $driver_id, $driverconfig)
 * @method \app\MarionetteCollection collection()
 * @method \app\MarionetteModel model()
 * @method \app\AcctgTransactionOperationCollection filter($conditions)
 */
class AcctgTransactionOperationCollection extends \mjolnir\accounting\AcctgTransactionOperationCollection
{
	/** @return \app\AcctgTransactionOperationCollection */
	static function instance($db = null) { return parent::instance($db); }
}

class AcctgTransactionOperationLib extends \mjolnir\accounting\AcctgTransactionOperationLib
{
	/** @return \app\Validator */
	static function check(array $fields, $context = null) { return parent::check($fields, $context); }
	/** @return \app\MarionetteModel */
	static function marionette_model() { return parent::marionette_model(); }
	/** @return \app\MarionetteCollection */
	static function marionette_collection() { return parent::marionette_collection(); }
	/** @return \app\Validator */
	static function update_check($id, array $fields) { return parent::update_check($id, $fields); }
	/** @return \app\SQLStatement */
	static function statement($identifier, $sql, $lang = null) { return parent::statement($identifier, $sql, $lang); }
}

/**
 * @method \app\Validator auditor()
 * @method \app\AcctgTransactionOperationModel do_patch($id, array $entry)
 * @method \app\AcctgTransactionOperationModel delete($id)
 * @method \app\AcctgTransactionOperationModel registerdriver($driver_id, $driver)
 * @method \app\MarionetteDriver getdriver($field, $driver_id, $driverconfig)
 * @method \app\MarionetteModel collection()
 * @method \app\MarionetteModel model()
 * @method \app\AcctgTransactionOperationModel filter($conditions)
 */
class AcctgTransactionOperationModel extends \mjolnir\accounting\AcctgTransactionOperationModel
{
	/** @return \app\AcctgTransactionOperationModel */
	static function instance($db = null) { return parent::instance($db); }
}
trait Trait_AcctgContext { use \mjolnir\accounting\Trait_AcctgContext; }
