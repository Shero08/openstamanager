<?php

if (isset($id_record)) {
    $record = $dbo->fetchOne('SELECT * FROM `co_rivalse` WHERE id='.prepare($id_record));
}
