<?php return array
	(
		'description'
			=> 'Install for AcctgTAccount, AcctgJournal and AcctgTransaction.',

		'configure' => array
			(
				'tables' => array
					(
						\app\AcctgTAccountTypeHintLib::table(),
						\app\AcctgTAccountTypeLib::table(),
						\app\AcctgTAccountLib::table(),
						\app\AcctgJournalLib::table(),
						\app\AcctgTransactionLib::table(),
						\app\AcctgTransactionOperationLib::table()
					),
			),

		'tables' => array
			(
				\app\AcctgTAccountTypeHintLib::table() =>
					'
						`id`       :key_primary,
						`slugid`   :slugid                               comment "Identifier for use when referencing in code.",
						`title`    :title                                comment "Type unique and clean name.",

						PRIMARY KEY (id)
					',

				\app\AcctgTAccountTypeLib::table() =>
					'
						`id`       :key_primary,
						`slugid`   :slugid	                             comment "Identifier for use when referencing in code.",
						`title`    :title                                comment "Type unique and clean name.",
						`typehint` :key_foreign                          comment "Pseudo-category name for use in user interfaces.",

						PRIMARY KEY (id)
					',

				\app\AcctgTAccountLib::table() =>
					'
						`id`        :key_primary,
						`type`	    :key_foreign                         comment "Account type; used to determine place in formulas.",
						`sign`      tinyint DEFAULT +1                   comment "Account value sign; used in formulas. Contra accounts have a negative sign.",

						PRIMARY KEY(id)
					',

				\app\AcctgJournalLib::table() =>
					'
						`id`    :key_primary,
						`title` :title                                   comment "Journal name.",
						`user`  :key_foreign                             comment "User responsible for the creation of the journal.",

						PRIMARY KEY(id)
					',

				\app\AcctgTransactionLib::table() =>
					'
						`id`        :key_primary,
						`journal`   :key_foreign                         comment "Journal transaction belongs to.",
						`user`      :key_foreign                         comment "User responsible for the creation of the journal.",
						`date`      :datetime_required                   comment "Date assigned to transaction; user selected, as in classical accounting journal terms.",
						`timestamp` :datetime_required                   comment "The real time the transaction was created for maintanence purposes.",

						PRIMARY KEY(id)
					',

				\app\AcctgTransactionOperationLib::table() =>
					'
						`id`           :key_primary,
						`operation`    shortint DEFAULT 0                comment "Debit operation (-1) or Credit operation (1)"
						`account`      :key_foreign                      comment "TAccount for the entry."
						`amount_value` :currency                         comment "Ammount value."
						`amount_type`  varchar(3) DEFAULT "USD"          comment "Amount type. By default USD. Operations wont convert; conversion will only happen globally."
					'
			),

		'bindings' => array
			(
				// field => [ table, on_delete, on_update ]

				\app\AcctgTAccountTypeLib::table() => array
					(
						'typehint' => [\app\AcctgTAccountTypeHintLib::table(), 'RESTRICT', 'CASCADE'],
					),
				\app\AcctgTAccountLib::table() => array
					(
						'type' => [\app\AcctgTAccountTypeLib::table(), 'SET NULL', 'CASCADE'],
					),
				\app\AcctgJournalLib::table() => array
					(
						'user' => [\app\Model_User::table(), 'SET NULL', 'CASCADE'],
					),
				\app\AcctgTransactionLib::table() => array
					(
						'journal' => [\app\AcctgJournalLib::table(), 'RESTRICT', 'CASCADE'],
						'user' => [\app\Model_User::table(), 'SET NULL', 'CASCADE'],
					),
				\app\AcctgTransactionOperationLib::table() => array
					(
						'account' => [\app\AcctgTransactionLib::table(), 'RESTRICT', 'CASCADE'],
					)
			),

	); # config