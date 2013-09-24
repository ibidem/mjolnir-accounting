<?php return array
	(
		'name' => 'acctg journal',
		'table' => 'acctg__journals',

		'key' => 'id',

		'fields' => array
			(
				'id' => 'number',
				'title' => 'string',
			// user who created the transaction
				'user' => array
					(
						'driver' => 'reference',
						'collection' => 'UserCollection'
					),
			),

	); # config
