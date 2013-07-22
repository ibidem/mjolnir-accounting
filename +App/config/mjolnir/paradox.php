<?php return array
	(
		'mjolnir-accounting' => array
			(
				'database' => 'default',

				// versions
				'1.0.0' => \app\Pdx::gate
					(
						'mjolnir-accounting/1.0.0',
						[
							'mjolnir-database' => '1.0.0',
						]
					),
			),

	); # config