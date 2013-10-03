<?php return array
	(
		'description'
			=> 'Install for AcctgTAccountTypeHint, AcctgTAccountType, AcctgTAccount, AcctgJournal, AcctgTransaction and AcctgTransactionOperation.',

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
						`title`     :title,
						`sign`      tinyint DEFAULT +1                   comment "Account value sign; used in formulas. Contra accounts have -1, non-contra accounts have +1.",
						`lft`       :nestedsetindex                      comment "Left position in Nested Set.",
						`rgt`       :nestedsetindex                      comment "Right position in Nested Set.",

						PRIMARY KEY (id)
					',

				\app\AcctgJournalLib::table() =>
					'
						`id`    :key_primary,
						`title` :title                                   comment "Journal name.",
						`user`  :key_foreign                             comment "User responsible for the creation of the journal.",

						PRIMARY KEY (id)
					',

				\app\AcctgTransactionLib::table() =>
					'
						`id`        :key_primary,
						`journal`   :key_foreign                         comment "Journal transaction belongs to.",
						`user`      :key_foreign                         comment "User responsible for the creation of the journal.",
						`memo`      :block                               comment "Comments on the transaction.",
						`date`      :datetime_required                   comment "Date assigned to transaction; user selected, as in classical accounting journal terms.",
						`timestamp` :datetime_required                   comment "The real time the transaction was created for maintanence purposes.",

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
						`memo`         :block                            comment "Operation details.",

						PRIMARY KEY (id)
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
						'transaction' => [\app\AcctgTransactionLib::table(), 'RESTRICT', 'CASCADE'],
						'taccount' => [\app\AcctgTransactionLib::table(), 'RESTRICT', 'CASCADE'],
					)
			),

		'populate' => function ($db)
			{
				// inject taccount type hints
				$hints = \app\Arr::trim(\app\CFS::config('timeline/mjolnir-accounting/1.0.0/taccount-type-hints'));
				\app\Pdx::massinsert
					(
						'mjolnir:accounting:inject-taccount-type-hints',
						$db, \app\AcctgTAccountTypeHintLib::table(),
						$hints,
						[
							'strs' => ['slugid', 'title'],
						]
					);

				// inject taccount types
				$taccount_type_hints = \app\Pdx::select($db, \app\AcctgTAccountTypeHintLib::table());
				$hintmapping = \app\Arr::gatherkeys($taccount_type_hints, 'slugid', 'id');
				$raw_taccount_types = \app\Arr::trim(\app\CFS::config('timeline/mjolnir-accounting/1.0.0/taccount-types'));
				$taccount_types = \app\Arr::applymapping($raw_taccount_types, 'typehint', $hintmapping);
				\app\Pdx::massinsert
					(
						'mjolnir:accounting:inject-taccount-types',
						$db, \app\AcctgTAccountTypeLib::table(),
						$taccount_types,
						[
							'nums' => ['typehint'],
							'strs' => ['slugid', 'title'],
						]
					);
			},

	); # config