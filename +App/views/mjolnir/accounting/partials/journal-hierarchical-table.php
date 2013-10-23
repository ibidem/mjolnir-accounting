<?
	namespace app;

	/* @var $lang Lang */

	$optypes = \app\CFS::config('mjolnir/types/acctg')['transaction-operation']['types'];

	$monthname = array
		(
			1 => 'Jan',
			2 => 'Feb',
			3 => 'Mar',
			4 => 'Apr',
			5 => 'May',
			6 => 'Jun',
			7 => 'Jul',
			8 => 'Aug',
			9 => 'Sep',
			10 => 'Oct',
			11 => 'Nov',
			12 => 'Dec',
		);

	isset($inline_style) or $inline_style = true;
?>

<? if ($inline_style): ?>
	<?= \app\View::instance('mjolnir/accounting/partials/journal-inline-style')
		->render() ?>
<? endif; ?>

<table class="table table-condensed acctg-journal-table">

	<thead class="acctg-journal-head">
		<tr>
			<th colspan="2">Date</th>
			<th>#id</th>
			<th>Accounts &amp; Description</th>
			<th>Debit</th>
			<th>Credit</th>
			<th>Notes</th>
			<th>&nbsp;</th>
		</tr>
	</thead>

	<?
		$last_year = null;
		$last_month = null;
		$last_day = null;
	?>

	<? if ( ! empty($records)): ?>
		<? foreach ($records as $year => $months): ?>
			<? if ($year !== $last_year): ?>
				<? $last_year = $year ?>
				<tbody>
					<tr>
						<td colspan="2"><?= $year ?></td>
					</tr>
				</tbody>
			<? endif; ?>
			<? foreach ($months as $month => $days): ?>
				<? $month = \intval($month) ?>
				<? foreach ($days as $day => $transactions): ?>
					<? foreach ($transactions as $transaction): ?>
						<tbody class="acctg-journal-table--transaction-tbody">
							<? $first_operation = true; ?>
							<? foreach ($transaction['operations'] as $operation): ?>
								<tr>
									<? if ($first_operation): ?>
										<? $first_operation = false; ?>
										<td class="acctg-journal-table--transaction-month">
											<? if ($month !== $last_month): ?>
												<? $last_month = $month ?>
												<?= $monthname[$month] ?>
											<? else: # unchanged ?>
												&nbsp;
											<? endif; ?>
										</td>
										<td class="acctg-journal-table--transaction-day">
											<? if ($day !== $last_day): ?>
												<? $last_day = $day ?>
												<?= $day ?>
											<? else: # unchanged ?>
												&nbsp;
											<? endif; ?>
										</td>
										<td>
											<a href="<?= $transaction['action'](null) ?>"><?= \sprintf('%010s', $transaction['id']) ?></a>
										</td>
									<? else: # not first row ?>
										<td colspan="3">&nbsp;</td>
									<? endif; ?>

									<? if ($operation['type'] == $optypes['debit']): ?>
										<td class="acctg-journal-table--debit-acct">
											<? # guarantee correct alignment; alignment has meaning ?>
											<div style="text-align: left;">
												<? $taccount = $context->acctgtaccount($operation['taccount']) ?>
												<a href="<?= $taccount['action'](null) ?>"><?= $taccount['title'] ?></a>
											</div>
										</td>
										<td><?= \app\Currency::format($operation['amount_value'], $operation['amount_type']) ?></td>
										<td>&nbsp;</td>
									<? else: # credit ?>
										<td class="acctg-journal-table--credit-acct">
											<? # guarantee correct alignment; alignment has meaning ?>
											<div style="text-align: right">
												<? $taccount = $context->acctgtaccount($operation['taccount']) ?>
												<a href="<?= $taccount['action'](null) ?>"><?= $taccount['title'] ?></a>
											</div>
										</td>
										<td>&nbsp;</td>
										<td><?= \app\Currency::format($operation['amount_value'], $operation['amount_type']) ?></td>
									<? endif; ?>
									<td class="acctg-journal-table--operation-note">
										<?= $operation['note'] ?>
									</td>
									<td>&nbsp;</td>
								</tr>
							<? endforeach; ?>
							<tr class="acctg-journal-table--description-row">
								<td colspan="3">&nbsp;</td>
								<td style="text-align: center;">
									<i><?= $transaction['description'] ?></i>
								</td>
								<td colspan="4">&nbsp;</td>
							</tr>
							<tr class="acctg-journal-table--delimiter-row">
								<td colspan="8">&nbsp;</td>
							</tr>
						</tbody>
					<? endforeach; ?>
				<? endforeach; ?>
			<? endforeach; ?>
		<? endforeach; ?>
	<? else: # empty years ?>
		<tbody class="acctg-journal-table--no-entries">
			<tr>
				<td colspan="6">
					<em>No entries available.</em>
				</td>
			</tr>
		</tbody>
	<? endif; ?>

</table>
