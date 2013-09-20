<?php return array
	(
		'mjolnir-accounting' => array
			(
				'database' => 'default',

				// versions
				'1.0.0' => \app\Pdx::gate
					(
						'mjolnir-accounting/install',
						[
							'mjolnir-database' => '1.0.0',
							'mjolnir-access' => '1.0.0'
						]
					),
			),

	); # config