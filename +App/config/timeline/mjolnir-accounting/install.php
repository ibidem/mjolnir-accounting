<?php return array
	(
		'description'
			=> 'Install for AcctgTAccountType, AcctgTAccount, AcctgTAccountLock, AcctgJournal, AcctgTransaction, AcctgTransactionLock and AcctgTransactionOperation.',

		'configure' => array
			(
				'tables' => array
					(
						\app\AcctgTAccountTypeLib::table(),
						\app\AcctgTAccountLib::table(),
						\app\AcctgTAccountLockLib::table(),
						\app\AcctgJournalLib::table(),
						\app\AcctgTransactionLib::table(),
						\app\AcctgTransactionLockLib::table(),
						\app\AcctgTransactionOperationLib::table(),
						\app\AcctgSettingsLib::table(),
					),
			),

		'tables' => array
			(
				\app\AcctgTAccountTypeLib::table() =>
					'
						`id`     :key_primary,
						`slugid` :slugid	                             comment "Identifier for use when referencing in code.",
						`title`  :title                                  comment "Type unique and clean name.",
						`sign`   tinyint DEFAULT +1                      comment "Formula sign, relative to parent account. (root types have +1)",
						`usable` :boolean                                comment "Usable indicates that the type is a hard type and not a logical type",
						`lft`    :nestedsetindex                         comment "Left position in Nested Set.",
						`rgt`    :nestedsetindex                         comment "Right position in Nested Set.",

						PRIMARY KEY (id)
					',

				\app\AcctgTAccountLib::table() =>
					'
						`id`        :key_primary,
						`group`     :key_foreign                         comment "Accounting group.",
						`type`	    :key_foreign                         comment "Account type; used to determine place in formulas.",
						`title`     :title,
						`sign`      tinyint DEFAULT +1                   comment "Account value sign; used in formulas. Contra accounts have -1, non-contra accounts have +1.",
						`lft`       :nestedsetindex                      comment "Left position in Nested Set.",
						`rgt`       :nestedsetindex                      comment "Right position in Nested Set.",

						PRIMARY KEY (id)
					',

				\app\AcctgTAccountLockLib::table() =>
					'
						`id`       :key_primary,
						`taccount` :key_foreign,
						`issuer`   :identifier                           comment "Identifier key, used to remove locks.",
						`cause`    :block                                comment "Subject to why the account is locked.",

						PRIMARY KEY (id)
					',

				\app\AcctgJournalLib::table() =>
					'
						`id`        :key_primary,
						`slugid`    :identifier DEFAULT NULL             comment "Special name given to specialized journals. Typically protected journals.",
						`title`     :title                               comment "Journal name.",
						`user`      :key_foreign                         comment "User responsible for the creation of the journal.",
						`protected` boolean                              comment "Some journals (eg. system journals) are protected; meaning they may not be deleted.",

						PRIMARY KEY (id)
					',

				\app\AcctgTransactionLib::table() =>
					'
						`id`          :key_primary,
						`group`       :key_foreign                       comment "Accounting group.",
						`journal`     :key_foreign                       comment "Journal transaction belongs to.",
						`method`      :identifier DEFAULT "manual"       comment "Method by which the entry was created. Only used in journal maintenance and entry migrations.",
						`user`        :key_foreign                       comment "User responsible for the creation of the journal.",
						`description` :block                             comment "Comments on the transaction.",
						`date`        :datetime_required                 comment "Date assigned to transaction; user selected, as in classical accounting journal terms.",
						`timestamp`   :datetime_required                 comment "The real time the transaction was created for maintanence purposes.",

						PRIMARY KEY (id)
					',

				\app\AcctgTransactionLockLib::table() =>
					'
						`id`          :key_primary,
						`transaction` :key_foreign,
						`issuer`      :identifier                        comment "Identifier key, used to remove locks.",
						`cause`       :block                             comment "Subject to why the transaction is locked.",

						PRIMARY KEY (id)
					',

				\app\AcctgTransactionOperationLib::table() =>
					'
						`id`           :key_primary,
						`transaction`  :key_foreign                      comment "The transaction for which the operation was performed.",
						`type`         tinyint DEFAULT 0                 comment "Debit operation (+1) or Credit operation (-1). Logic: Cr/Dr effect on asset accounts",
						`taccount`     :key_foreign                      comment "TAccount with which the transaction is associated.",
						`amount_value` :currency                         comment "Ammount value.",
						`amount_type`  varchar(3) DEFAULT "USD"          comment "Amount type. By default USD. Operations wont convert; conversion will only happen globally.",
						`note`         :block                            comment "Operation details.",

						PRIMARY KEY (id)
					',

				\app\AcctgSettingsLib::table() =>
					'
						`id`       :key_primary,
						`group`    :key_foreign                         comment "Accounting group.",
						`slugid`   :identifier,
						`taccount` :key_foreign,

						PRIMARY KEY (id)
					',
			),

		'bindings' => array
			(
				// field => [ table, on_delete, on_update ]

				\app\AcctgTAccountLib::table() => array
					(
						'type' => [\app\AcctgTAccountTypeLib::table(), 'SET NULL', 'CASCADE'],
					),
				\app\AcctgTAccountLockLib::table() => array
					(
						'taccount' => [\app\AcctgTAccountLib::table(), 'RESTRICT', 'CASCADE'],
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
				\app\AcctgTransactionLockLib::table() => array
					(
						'transaction' => [\app\AcctgTransactionLib::table(), 'CASCADE', 'CASCADE'],
					),
				\app\AcctgTransactionOperationLib::table() => array
					(
						'transaction' => [\app\AcctgTransactionLib::table(), 'CASCADE', 'CASCADE'],
						'taccount' => [\app\AcctgTAccountLib::table(), 'CASCADE', 'CASCADE'],
					),
				\app\AcctgSettingsLib::table() => array
					(
						'taccount' => [\app\AcctgTAccountLib::table(), 'CASCADE', 'CASCADE'],
					),
			),

		'populate' => function ($db)
			{
				\app\AcctgTAccountTypeLib::install($db);
				\app\AcctgJournalLib::install($db);
			},

	); # config