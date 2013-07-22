<?php return array
	(
		'name' => 'acctg transaction',

		'key' => 'id',

		'fields' => array
			(
				'id' => 'number',
			// user who created the transaction
				'user' => array
					(
						'driver' => 'reference',
						'collection' => 'UserCollection'
					),
			// journal in which the transaction is logged into
				'journal' => array
					(
						'driver' => 'reference',
						'collection' => 'AcctgJournalCollection'
					),
			// details on transaction
				'memo' => 'string',
			// the debit account
				'debit_account' => array
					(
						'driver' => 'reference',
						'collection' => 'AcctgTAccountCollection'
					),
			// the credit account
				'credit_account' => array
					(
						'driver' => 'reference',
						'collection' => 'AcctgTAccountCollection'
					),
			// the value moved by the transaction
				'amount' => array
					(
						'driver' => 'currency',
					),
			),

	);