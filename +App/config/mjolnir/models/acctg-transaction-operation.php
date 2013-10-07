<?php return array
	(
		'name' => 'acctg transaction operation',
		'table' => 'acctg__transaction_operations',

		'key' => 'id',

		'fields' => array
			(
				'id' => 'number',
			// debit = +1, credit = -1
			// logic: think of Dr/Cr as they affect asset accounts
				'type' => 'number',
			// the account for which the transaction was recorded
				'taccount' => array
					(
						'driver' => 'reference',
						'collection' => 'AcctgTAccountCollection'
					),
			// the transaction with which this operation is associated with
				'transaction' => array
					(
						'driver' => 'reference',
						'collection' => 'AcctgTransactionCollection'
					),
			// the amout moved by the operation
				'amount' => [ 'driver' => 'currency' ],
			// a note on the operation
				'note' => 'string',
			),

	); # config
