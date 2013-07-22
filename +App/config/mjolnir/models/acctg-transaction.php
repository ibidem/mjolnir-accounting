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
			// the date the transaction was recorded on
				'timestamp' => 'datetime',
			// the date for which the transaction was recorded
				'date' => 'datetime',
			// journal in which the transaction is logged into
				'journal' => array
					(
						'driver' => 'reference',
						'collection' => 'AcctgJournalCollection'
					),
			// details on transaction
				'memo' => 'string',
			// the account from which we debit
				'debit_account' => array
					(
						'driver' => 'reference',
						'collection' => 'AcctgTAccountCollection'
					),
			// the account to which we credit
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