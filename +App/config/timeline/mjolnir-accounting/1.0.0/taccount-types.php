<?php return array
	(
		#
		# Assets Account Types
		#

		'cash' => array
			(
				'slugid' => 'cash',
				'title' => 'Cash',
				'typehint' => 'assets-acct',
			),
		'banks' => array
			(
				'slugid' => 'banks',
				'title' => 'Banks',
				'typehint' => 'assets-acct',
			),
		'current-assets' => array
			(
				'slugid' => 'current-assets',
				'title' => 'Current Assets',
				'typehint' => 'assets-acct',
			),
		'long-term-assets' => array
			(
				'slugid' => 'long-term-assets',
				'title' => 'Long-Term Assets',
				'typehint' => 'assets-acct',
			),
		'depreciation' => array
			(
				'slugid' => 'depreciation',
				'title' => 'Depreciation',
				'typehint' => 'assets-acct',
			),

		#
		# Equity - Liability Account Types
		#

		'current-liabilities' => array
			(
				'slugid' => 'current-liabilities',
				'title' => 'Current Liabilities',
				'typehint' => 'liability-acct',
			),
		'long-term-liabilities' => array
			(
				'slugid' => 'long-term-liabilities',
				'title' => 'Long-Term Liabilities',
				'typehint' => 'liability-acct',
			),

		#
		# Equity - OE Account Types
		#

		'investments' => array
			(
				'slugid' => 'investments',
				'title' => 'Investments',
				'typehint' => 'oe-acct',
			),
		'capital-stock' => array
			(
				'slugid' => 'capital-stock',
				'title' => 'Capital Stock',
				'typehint' => 'oe-acct',
			),
		'retained-earnings' => array
			(
				'slugid' => 'retained-earnings',
				'title' => 'Retained Earnings',
				'typehint' => 'oe-acct',
			),

		#
		# Equity - Withdraws Account Types
		#

		'withdraws' => array
			(
				'slugid' => 'withdraws',
				'title' => 'Withdraws',
				'typehint' => 'withdraws-acct',
			),

		#
		# Equity - Revenue Account Types
		#

		'revenue' => array
			(
				'slugid' => 'revenue',
				'title' => 'Revenue',
				'typehint' => 'revenue-acct',
			),

		#
		# Equity - Expenses Account Types
		#

		'general-expenses' => array
			(
				'slugid' => 'general-expenses',
				'title' => 'General Expenses',
				'typehint' => 'expenses-acct',
			),
		'depreciation-expenses' => array
			(
				'slugid' => 'depreciation-expenses',
				'title' => 'Depreciation Expenses',
				'typehint' => 'expenses-acct',
			),

	); # config
