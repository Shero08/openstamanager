<?php

include_once __DIR__.'/../../core.php';

// Valori di ricerca
$search = [
    'codice' => $_GET['search_codice'],
    'descrizione' => $_GET['search_descrizione'],
    'categoria' => $_GET['search_categoria'],
    'subcategoria' => $_GET['search_subcategoria'],
    'tipo' => $_GET['search_tipo'],
];

foreach ($search as $name => $value) {
    if ($value == 'undefined') {
        $search[$name] = null;
    }
}

$search['tipo'] = $search['tipo'] ?: 'solo prodotti attivi';

// Filtri effettivi
$where = [
    'deleted_at IS NULL',
    'servizio = 0',
];
if ($search['tipo'] == 'solo prodotti attivi') {
    $where[] = 'attivo = 1';
} elseif ($search['tipo'] == 'solo prodotti non attivi') {
    $where[] = 'attivo = 0';
}

if (!empty($search['codice'])) {
    $where[] = "(REPLACE(codice, '.', '') LIKE ".prepare('%'.$search['codice'].'%').' OR codice LIKE '.prepare('%'.$search['codice'].'%').')';
}

if (!empty($search['descrizione'])) {
    $where[] .= "REPLACE(descrizione, '.', '') LIKE ".prepare('%'.$search['descrizione'].'%');
}

if (!empty($search['categoria'])) {
    $where[] = 'id_categoria IN (SELECT id FROM mg_categorie WHERE descrizione LIKE '.prepare('%'.$search['categoria'].'%').')';
}

if (!empty($search['subcategoria'])) {
    $where[] = 'id_sottocategoria IN (SELECT id FROM mg_categorie WHERE descrizione LIKE '.prepare('%'.$search['subcategoria'].'%').')';
}

$period_end = $_SESSION['period_end'];

$query = 'SELECT *,
       (SELECT SUM(qta) FROM mg_movimenti WHERE mg_movimenti.idarticolo=mg_articoli.id AND (mg_movimenti.idintervento IS NULL) AND data <= '.prepare($period_end).') AS qta
FROM mg_articoli WHERE 1=1
ORDER BY codice ASC';

$query = str_replace('1=1', '1=1'.(!empty($where) ? ' AND '.implode(' AND ', $where) : ''), $query);
$rs = $dbo->fetchArray($query);

echo '
<h3>'.tr('Inventario al _DATE_', [
    '_DATE_' => dateFormat($period_end),
], ['upper' => true]).'</h3>

<table class="table table-bordered">
    <thead>
        <tr>
            <th class="text-center" width="150">'.tr('Codice', [], ['upper' => true]).'</th>
            <th class="text-center">'.tr('Descrizione', [], ['upper' => true]).'</th>
            <th class="text-center" width="70">'.tr('Prezzo di vendita', [], ['upper' => true]).'</th>
            <th class="text-center" width="70">'.tr('Q.tà', [], ['upper' => true]).'</th>
            <th class="text-center" width="70">'.tr('Prezzo di acquisto', [], ['upper' => true]).'</th>
            <th class="text-center" width="90">'.tr('Valore totale', [], ['upper' => true]).'</th>
        </tr>
    </thead>

    <tbody>';

$totali = [];
foreach ($rs as $r) {
    $valore_magazzino = $r['prezzo_acquisto'] * $r['qta'];

    echo '
        <tr>
            <td>'.$r['codice'].'</td>
            <td>'.$r['descrizione'].'</td>
            <td class="text-right">'.moneyFormat($r['prezzo_vendita']).'</td>
            <td class="text-right">'.numberFormat($r['qta']).' '.$r['um'].'</td>
            <td class="text-right">'.moneyFormat($r['prezzo_acquisto']).'</td>
            <td class="text-right">'.moneyFormat($valore_magazzino).'</td>
        </tr>';

    $totali[] = $valore_magazzino;
}

// Totali
$totale_acquisto = sum($totali);
$totale_qta = sum(array_column($rs, 'qta'));
echo '
    </tbody>

    <tr>
        <td colspan="2" class="text-right border-top"><b>'.tr('Totale', [], ['upper' => true]).':</b></td>
        <td class="border-top"></td>
        <td class="text-right border-top"><b>'.numberFormat($totale_qta).'</b></td>
        <td class="border-top"></td>
        <td class="text-right border-top"><b>'.moneyFormat($totale_acquisto).'</b></td>
    </tr>
</table>';
