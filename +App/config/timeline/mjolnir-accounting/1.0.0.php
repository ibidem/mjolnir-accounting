<?php return array
	(
		'description'
			=> 'Install for AcctgTAccount, AcctgJournal and AcctgTransaction.',

		'configure' => array
			(
				'tables' => array
					(
						\app\AcctgTAccountLib::table(),
						\app\AcctgJournalLib::table(),
						\app\AcctgTransactionLib::table(),
					),
			),

		'tables' => array
			(
				\app\AcctgTAccountLib::table() =>
					'
						`id`        :key_primary,
						`type`	    :title,
						`influence` tinyint,

						PRIMARY KEY(id)
					',

				\app\AcctgJournalLib::table() =>
					'
						`id`    :key_primary,
						`title` :title,

						PRIMARY KEY(id)
					',

				\app\AcctgTransactionLib::table() =>
					'
						`id`             :key_primary,
						`user`           :key_foreign,
						`timestamp`      :datetime_required,
						`date`           :datetime_required,
						`journal`        :key_foreign,
						`debit_account`  :key_foreign,
						`credit_account` :key_foreign,
						`amount_value`   :currency,
						`amount_type`    varchar(3),

						PRIMARY KEY(id)
					',
			),

		'bindings' => array
			(
				\app\AcctgTransactionLib::table() => array
					(
						'user' => [ \app\Model_User::table(), 'SET NULL', 'CASCADE' ],
						'journal' => [ \app\AcctgJournalLib::table(), 'SET NULL', 'CASCADE' ],
						'debit_account' => [ \app\AcctgTAccountLib::table(), 'SET NULL', 'CASCADE' ],
						'credit_account' => [ \app\AcctgTAccountLib::table(), 'SET NULL', 'CASCADE' ],
					),
			),

	); # config