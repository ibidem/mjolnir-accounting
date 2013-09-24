<?php return array
	(
		'name' => 'acctg taccount type',
		'table' => 'acctg__taccount_types',

		'key' => 'id',

		'fields' => array
			(
				'id' => 'number',
				'title' => 'string',
			// code reference name
				'slugid' => 'string',
			// extra information for user interface output
				'typehint' => array
					(
						'driver' => 'reference',
						'collection' => 'AcctgTAccountTypeHintCollection'
					),
			),

	); # config
