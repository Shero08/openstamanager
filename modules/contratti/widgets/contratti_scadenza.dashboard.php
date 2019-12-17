<?php

$rs = $dbo->fetchArray('SELECT *,
       ((SELECT SUM(co_righe_contratti.qta) FROM co_righe_contratti WHERE co_righe_contratti.um=\'ore\' AND co_righe_contratti.idcontratto=co_contratti.id) - IFNULL( (SELECT SUM(in_interventi_tecnici.ore) FROM in_interventi_tecnici INNER JOIN in_interventi ON in_interventi_tecnici.idintervento=in_interventi.id WHERE in_interventi.id_contratto=co_contratti.id AND in_interventi.id_stato IN (SELECT in_statiintervento.id_stato FROM in_statiintervento WHERE in_statiintervento.completato = 1)), 0) ) AS ore_rimanenti,
       DATEDIFF(data_conclusione, NOW()) AS giorni_rimanenti,
       data_conclusione,
       ore_preavviso_rinnovo,
       giorni_preavviso_rinnovo,
       (SELECT ragione_sociale FROM an_anagrafiche WHERE idanagrafica=co_contratti.idanagrafica) AS ragione_sociale
FROM co_contratti WHERE
        idstato IN (SELECT id FROM co_staticontratti WHERE is_fatturabile = 1) AND
        rinnovabile = 1 AND
        YEAR(data_conclusione) > 1970 AND
        (SELECT id FROM co_contratti contratti WHERE contratti.idcontratto_prev = co_contratti.id) IS NULL
HAVING (ore_rimanenti < ore_preavviso_rinnovo OR DATEDIFF(data_conclusione, NOW()) < ABS(giorni_preavviso_rinnovo))
ORDER BY giorni_rimanenti ASC, ore_rimanenti ASC');

if (!empty($rs)) {
    echo '
<table class="table table-hover">
    <tr>
        <th width="50%">'.tr('Contratto').'</th>
        <th width="15%" class="text-center">'.tr('Data inizio').'</th>
        <th width="15%" class="text-center">'.tr('Data conclusione').'</th>
        <th width="20%">'.tr('Rinnovo').'</th>
    </tr>';

    foreach ($rs as $r) {
        $data_accettazione = !empty($r['data_accettazione']) ? dateFormat($r['data_accettazione']) : '';

        $data_conclusione = !empty($r['data_conclusione']) ? dateFormat($r['data_conclusione']) : '';

        // Se scaduto, segna la riga in rosso
        $class = (strtotime($r['data_conclusione']) < strtotime(date('Y-m-d')) && !empty($data_conclusione)) ? 'danger' : '';

        if (isset($r['ore_rimanenti'])) {
            // Se ore finite, segna la riga in rosso
            if ($class != 'danger') {
                $class = ($r['ore_rimanenti'] < 0) ? 'warning' : '';
            }

            $ore_rimanenti = ($r['ore_rimanenti'] >= 0) ? tr('ore rimanenti: _NUM_') : tr('ore in aggiunta: _NUM_');
            $ore_rimanenti = str_replace('_NUM_', abs($r['ore_rimanenti']), $ore_rimanenti);
        }

        $scadenza = ($r['giorni_rimanenti'] > 0) ? tr('scade tra _DAYS_ giorni') : tr('scaduto da _DAYS_ giorni');
        $scadenza = str_replace('_DAYS_', abs($r['giorni_rimanenti']), $scadenza);

        echo '
    <tr class="'.$class.'">
        <td>
            '.module('Contratti')->link($r['id'], $r['nome']).'<br>
            <small class="form-text">'.$r['ragione_sociale'].'</small>
        </td>
        <td class="text-center">'.$data_accettazione.'</td>
        <td class="text-center">'.$data_conclusione.'</td>
        <td>'.$scadenza.(isset($r['ore_rimanenti']) ? ' ('.$ore_rimanenti.')' : '').'</td>
    </tr>';
    }
    echo '
</table>';
} else {
    echo '
<p>'.tr('Non ci sono contratti in scadenza').'.</p>';
}
