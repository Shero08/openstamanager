<?php

if (isset($id_record)) {
    $record = $dbo->fetchOne('SELECT * FROM an_referenti WHERE id='.prepare($id_record));
}
