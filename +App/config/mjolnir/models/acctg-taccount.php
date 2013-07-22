<?php return array
	(
		'name' => 'acctg taccount',

		'key' => 'id',

		'fields' => array
			(
				'id' => 'number',
			// name of the account
				'title' => 'string',
			// type of the account: asset, equity
				'type' => 'string',
			// influence on the account type, +1 or -1
			// ie. in A = L + OE - W + R - E, on the equity side [W]ithdrawls
			// and [E]xpenses have negative influence on the balance as can
			// be seen in the equation
				'influence' => 'number',
			),

	);