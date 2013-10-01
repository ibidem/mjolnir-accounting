<?php return array
	(
		'name' => 'acctg taccount',
		'table' => 'acctg__taccounts',

		'key' => 'id',

		'fields' => array
			(
				'id' => 'number',
				'type' => 'number',
				'title' => 'string',
			// account value sign; used in formulas; contra accounts have -1
			// and non-contra accounts have +1
				'sign' => 'number',
			// nested set indexes
				'lft' => 'number',
				'rgt' => 'number',
			),

		'errors' => array
			(
				'new_parent' => array
					(
						'compatible-taccount-type' => \app\Lang::key('mjolnir:acctg/taccount/incompatible-taccount-type'),
						'not-recursive' => \app\Lang::key('mjolnir:acctg/taccount/recusive-tree-move'),
					),
				'parent' => array
					(
						'valid-parent-type' => \app\Lang::key('mjolnir:acctg/taccount/incompatible-parent-type')
					)
			),

	); # config
