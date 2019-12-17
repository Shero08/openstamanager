<?php

echo '
<form action="" method="post" id="edit-form">
	<input type="hidden" name="backto" value="record-edit">
	<input type="hidden" name="op" value="update">

	<!-- DATI -->
	<div class="card card-primary">
		<div class="card-header">
            <h3 class="card-title">'.tr('Impostazioni _SEZIONE_', [
                '_SEZIONE_' => $record['sezione'],
            ]).'</h3>
		</div>

		<div class="card-body row">';

foreach ($records as $record) {
    echo '
            <div class="col-md-6">
                '.\Models\Setting::get($record['id'])->input().'
            </div>';
}

echo '
			<div class="clearfix"></div><hr>
            <div class="float-right">
				<button type="submit" class="btn btn-success"><i class="fa fa-check"></i> '.tr('Salva modifiche').'</button>
			</div>
		</div>
	</div>

</form>';
