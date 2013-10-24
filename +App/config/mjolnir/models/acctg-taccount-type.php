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
			// formula sign relative to parent type (root types have +1)
			// the formula sign premits full TAccount tree validation
				'sign' => 'number',
			// usable indicates if the user should be allowed to select the
			// type; if the type not usable it is considered logical, ie. used
			// in filtering, displaying, etc
				'usable' => 'boolean',
			// nested set indexes
				'lft' => 'number',
				'rgt' => 'number',
			),

	); # config
