<?php

if (isset($id_record)) {
    $record = $dbo->fetchOne('SELECT * FROM in_vociservizio WHERE id='.prepare($id_record));
}
