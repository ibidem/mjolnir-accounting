<?php return array
	(
		'name' => 'acctg journal',
		'table' => 'acctg__journals',

		'key' => 'id',

		'fields' => array
			(
				'id' => 'number',
				'title' => 'string',
				'slugid' => 'string',
				'protected' => 'boolean',
			// user who created the transaction
				'user' => array
					(
						'driver' => 'reference',
						'collection' => 'UserCollection'
					),
			),

	); # config
