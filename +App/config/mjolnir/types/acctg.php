<?php return array
	(
		#
		# This file maps various system values for safe programatic
		# computations. Essentially we need to store values as integers at
		# every oportunity, primarily for operation efficiency rather then
		# space saving, but we also need to safely use them.
		#

		'transaction-operation' => array
			(
				'types' => array
					(
						'debit' => +1,
						'credit' => -1,
					),
			),

		'transaction-method' => array
			(
				'manual' => 'Manual',
				'anonymous' => 'Unknown Entity'
			),

		'report' => array
			(
				'intervals' => array
					(
						'custom' => 'Custom',
						'all' => 'All',
						'today' => 'Today',
						'current-month' => 'Current Month',
//						'fiscal-quarter' => 'Fiscal Quarter',
//						'fiscal-year' => 'Fiscal Year',
//						'last-fiscal-year' => 'Last Fiscal Year',
					),
				'breakdowns' => array
					(
						'totals-only' => 'Totals Only',
						'month' => 'Monthly',
//						'quarter' => 'Quarterly',
//						'year' => 'Yearly',
					),
			),

	); # config
