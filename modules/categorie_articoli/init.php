<?php

include_once __DIR__.'/../../core.php';

if (isset($id_record)) {
    $record = $dbo->fetchOne('SELECT * FROM `mg_categorie` WHERE id='.prepare($id_record));
}
