<?php

$rs = $dbo->fetchArray('SELECT * FROM in_interventi WHERE in_interventi.id_stato = (SELECT in_statiintervento.id FROm in_statiintervento WHERE in_statiintervento.codice=\'TODO\') ORDER BY data_richiesta ASC');

if (!empty($rs)) {
    echo '
<table class="table table-hover">
    <tr>
        <th width="50%">'.tr('Attività').'</th>
        <th width="15%">'.tr('Data richiesta').'</th>
    </tr>';

    foreach ($rs as $r) {
        $data_richiesta = !empty($r['data_richiesta']) ? dateFormat($r['data_richiesta']) : '';

        echo '
    <tr >
        <td>
            '.module('Interventi')->link($r['id'], 'Intervento n. '.$r['codice'].' del '.$data_richiesta).'<br>
            <small class="form-text">'.$r['ragione_sociale'].'</small>
        </td>
        <td class="text-center">'.$data_richiesta.'</td>
    </tr>';
    }
    echo '
</table>';
} else {
    echo '
<p>'.tr('Non ci sono attività da programmare').'.</p>';
}
