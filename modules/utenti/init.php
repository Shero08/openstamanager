<?php

if (isset($id_record)) {
    $record = $dbo->fetchOne('SELECT * FROM `zz_groups` WHERE `id`='.prepare($id_record));
}
