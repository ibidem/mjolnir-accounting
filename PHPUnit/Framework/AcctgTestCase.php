<?php namespace mjolnir\accounting;

/**
 * @package    mjolnir
 * @category   PHPUnit
 * @author     Ibidem Team
 * @copyright  (c) 2013, Ibidem Team
 * @license    https://github.com/ibidem/ibidem/blob/master/LICENSE.md
 */
class PHPUnit_Framework_AcctgTestCase extends \app\PHPUnit_Framework_TestCase
{
	function setUp()
	{
		\app\AcctgTransactionLib::truncate();
		\app\AcctgTransactionOperationLib::truncate();
	}

	function tearDown()
	{
		// empty
	}

	static function add_transaction($req)
	{
		$db = \app\SQLDatabase::instance('default');
		$db->begin();
		try
		{
			$transactions = \app\AcctgTransactionCollection::instance($db);

			$transaction = $transactions->post
				(
					[
						'method' => 'manual',
						'journal' => $req['journal'],
						'description' => $req['description'],
						'date' => $req['date'],
						# sign-off
						'timestamp' => \date('Y-m-d H:i:s'),
						'user' => \app\Auth::id(),
					]
				);

			if ($transaction === null)
			{
				throw new \Exception('Failed to create acctg transaction.');
			}

			$transaction['operations'] = [];

			$operations = \app\AcctgTransactionOperationCollection::instance($db);

			foreach ($req['operations'] as $req_op)
			{
				$operation = $operations->post
					(
						[
							'transaction' => $transaction['id'],
							'type' => $req_op['type'],
							'taccount' => $req_op['taccount'],
							'note' => $req_op['note'],
							'amount' => array
								(
									'value' => $req_op['amount_value'],
									'type' => $req_op['amount_type'],
								),
						]
					);

				if ($operation === null)
				{
					throw new \Exception('Failed to create acctg transaction operation.');
				}

				$transaction['operations'][] = $operation;
			}

			$db->commit();
		}
		catch (\app\Exception_NotApplicable $e)
		{
			$db->rollback();
			throw new \app\Exception_APIError($e->getMessage());
		}
		catch (\Exception $e)
		{
			$db->rollback();
			throw $e;
		}

		return $transaction;
	}

} # class
