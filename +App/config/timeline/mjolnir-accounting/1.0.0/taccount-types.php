<?php return array
	(
		'assets' => array
			(
				'slugid' => 'assets',
				'title' => 'Asset Accounts',
				'parent' => null,
				'sign' => +1,
				'usable' => false,
			),

			'cash' => array
				(
					'slugid' => 'cash',
					'title' => 'Cash',
					'parent' => 'assets',
					'sign' => +1,
					'usable' => true,
				),

				'bank' => array
					(
						'slugid' => 'bank',
						'title' => 'Bank',
						'parent' => 'cash',
						'sign' => +1,
						'usable' => true,
					),

			'current-assets' => array
				(
					'slugid' => 'current-assets',
					'title' => 'Current Assets',
					'parent' => 'assets',
					'sign' => +1,
					'usable' => true,
				),

			'long-term-assets' => array
				(
					'slugid' => 'long-term-assets',
					'title' => 'Long-Term Assets',
					'parent' => 'assets',
					'sign' => +1,
					'usable' => true,
				),

			'depreciation' => array
				(
					'slugid' => 'depreciation',
					'title' => 'Depreciation',
					'parent' => 'assets',
					'sign' => +1,
					'usable' => true,
				),

		'equity' => array
			(
				'slugid' => 'equity',
				'title' => 'Equity Accounts',
				'parent' => null,
				'sign' => +1,
				'usable' => false,
			),

			'liabilities' => array
				(
					'slugid' => 'liabilities',
					'title' => 'Liabilities',
					'parent' => 'equity',
					'sign' => +1,
					'usable' => false,
				),

				'current-liabilities' => array
					(
						'slugid' => 'current-liabilities',
						'title' => 'Current Liabilities',
						'parent' => 'liabilities',
						'sign' => +1,
						'usable' => true,
					),
				'long-term-liabilities' => array
					(
						'slugid' => 'long-term-liabilities',
						'title' => 'Long-Term Liabilities',
						'parent' => 'liabilities',
						'sign' => +1,
						'usable' => true,
					),

			'owner-equity' => array
				(
					'slugid' => 'owner-equity',
					'title' => 'Owner\'s Equity',
					'parent' => 'equity',
					'sign' => +1,
					'usable' => false,
				),

				'capital-stock' => array
					(
						'slugid' => 'capital-stock',
						'title' => 'Capital Stock',
						'parent' => 'owner-equity',
						'sign' => +1,
						'usable' => true,
					),

				'investments' => array
					(
						'slugid' => 'investments',
						'title' => 'Investments',
						'parent' => 'owner-equity',
						'sign' => +1,
						'usable' => true,
					),

				'withdrawals' => array
					(
						'slugid' => 'withdrawals',
						'title' => 'Withdrawals',
						'parent' => 'owner-equity',
						'sign' => -1,
						'usable' => true,
					),

				'retained-earnings' => array
					(
						'slugid' => 'retained-earnings',
						'title' => 'Retained Earnings',
						'parent' => 'owner-equity',
						'sign' => +1,
						'usable' => true,
					),

			'revenue' => array
				(
					'slugid' => 'revenue',
					'title' => 'Revenue',
					'parent' => 'equity',
					'sign' => +1,
					'usable' => true,
				),

			'expenses' => array
				(
					'slugid' => 'expenses',
					'title' => 'Expenses',
					'parent' => 'equity',
					'sign' => -1,
					'usable' => false,
				),

				'general-expenses' => array
					(
						'slugid' => 'general-expenses',
						'title' => 'General Expenses',
						'parent' => 'expenses',
						'sign' => +1,
						'usable' => true,
					),

				'depreciation-expenses' => array
					(
						'slugid' => 'depreciation-expenses',
						'title' => 'Depreciation Expenses',
						'parent' => 'expenses',
						'sign' => +1,
						'usable' => true,
					),

	); # config
