<?php return array
	(
		'name' => 'acctg transaction operation',
		'table' => 'acctg__transaction_operations',

		'key' => 'id',

		'fields' => array
			(
				'id' => 'number',
			// debit = -1, credit = +1
				'operation' => 'number',
			// the date for which the transaction was recorded
				'taccount' => array
					(
						'driver' => 'reference',
						'collection' => 'AcctgTAccountCollection'
					),
			// the amout moved by the operation
				'amount' => [ 'driver' => 'currency' ],
			),

	); # config
