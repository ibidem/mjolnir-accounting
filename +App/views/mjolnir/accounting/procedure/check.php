<?
	namespace app;

	/* @var $lang Lang */
?>

<?= $f = HTML::form($control->action('process'), 'mjolnir:twbs3') ?>

<div class="form-horizontal">

	<?= $f->date('Date', 'date')
		->value_is(\date('Y-m-d')) ?>

	<?= $f->select('Account', 'taccount')
		->options_liefhierarchy($driver->options_taccounts())
		->render() ?>

	<?= $f->select('Pay to the order of', 'orderof')
		->options_liefhierarchy($driver->options_orderof()) ?>

	<?= $f->text('Amount', 'amount') ?>

	<?= $f->text('Amount Literal', 'literal_amount') ?>

	<hr/>

	<div class="form-group">
		<div class="col-lg-offset-2 col-lg-10">
			<button class="btn btn-primary btn-large" type="submit" <?= $f->mark() ?>>
				Record
			</button>
		</div>
	</div>

</div>