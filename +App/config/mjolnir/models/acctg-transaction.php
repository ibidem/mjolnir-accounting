<?php return array
	(
		'name' => 'acctg transaction',

		'key' => 'id',

		'fields' => array
			(
				'id' => 'number',
			// journal in which the transaction is logged into
				'journal' => array
					(
						'driver' => 'reference',
						'collection' => 'AcctgJournalCollection'
					),
			// user who created the transaction
				'user' => array
					(
						'driver' => 'reference',
						'collection' => 'UserCollection'
					),
			// details on transaction
				'comments' => 'string',
			// the date for which the transaction was recorded
				'date' => 'datetime',
			// the date the transaction was recorded on
				'timestamp' => 'datetime',
			),

	); # config
