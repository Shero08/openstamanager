<?php

use Modules\Ordini\Ordine;

$documento = Ordine::find($id_record);

$module = module($documento->module);

if (get('documento') == 'fattura') {
    $final_module = $module['name'] == 'Ordini cliente' ? 'Fatture di vendita' : 'Fatture di acquisto';
    $op = 'add_documento';
} else {
    $final_module = $module['name'] == 'Ordini cliente' ? 'Ddt di vendita' : 'Ddt di acquisto';
    $op = 'add_ordine';
}

$options = [
    'op' => $op,
    'type' => 'ordine',
    'module' => $final_module,
    'button' => tr('Aggiungi'),
    'create_document' => true,
    'serials' => true,
    'documento' => $documento,
];

echo App::load('importa.php', [], $options, true);
