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
			),

	); # config
