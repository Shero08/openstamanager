<?php

include_once __DIR__.'/../../core.php';

if (isset($id_record)) {
    // Aggiornamento stato di questo ordine (?)
    if (!empty(get_stato_ordine($id_record)) && setting('Cambia automaticamente stato ordini fatturati')) {
        $dbo->query('UPDATE or_ordini SET id_statoordine=(SELECT id FROM or_statiordine WHERE descrizione="'.get_stato_ordine($id_record).'") WHERE id='.prepare($id_record));
    }

    $record = $dbo->fetchOne('SELECT *, or_ordini.note, or_ordini.idpagamento, or_ordini.id AS idordine, or_statiordine.descrizione AS `stato`, or_tipiordine.descrizione AS `descrizione_tipodoc`, (SELECT completato FROM or_statiordine WHERE or_statiordine.id=or_ordini.id_statoordine) AS flag_completato FROM ((or_ordini LEFT OUTER JOIN or_statiordine ON or_ordini.id_statoordine=or_statiordine.id) INNER JOIN an_anagrafiche ON or_ordini.idanagrafica=an_anagrafiche.idanagrafica) INNER JOIN or_tipiordine ON or_ordini.idtipoordine=or_tipiordine.id WHERE or_ordini.id='.prepare($id_record));
}
