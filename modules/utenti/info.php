<?php

$skip_permissions = true;
$pageTitle = tr('Utente');

include_once App::filepath('resources\views|custom|\layout', 'top.php');

if (post('op') == 'self_update') {
    include_once __DIR__.'/actions.php';
}

$user = Auth::user();
$token = auth()->getToken();

$rs = $dbo->fetchArray('SELECT * FROM an_anagrafiche WHERE idanagrafica = '.prepare($user['idanagrafica']));
$anagrafica = [];
if (!empty($rs)) {
    $anagrafica = $rs[0];
}

$api = BASEURL.'/api/?token='.$token;
$module = module('Utenti e permessi');

echo '
<div class="card card-outline card-widget widget-user">
    <div class="widget-user-header bg-'.(($theme != 'default') ? $theme : 'primary').'">
      <h3 class="widget-user-username">'.$user['username'].'</h3>
      <h5 class="widget-user-desc">'.$user['gruppo'].'</h5>
    </div>

    <div class="widget-user-image">';

$user_photo = $user->photo;
if ($user_photo) {
    echo '
        <img src="'.$user_photo.'" class="img-circle" alt="'.$user['username'].'" />';
} else {
    echo '
        <i class="fa fa-user-circle-o fa-4x pull-left" alt="'.tr('OpenSTAManager').'"></i>';
}

echo '
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-sm-4 border-right">
                <div class="description-block">
                    <h5 class="description-header">'.tr('Anagrafica associata').'</h5>
                    <span class="description-text">'.(!empty($anagrafica) ? $anagrafica['ragione_sociale'] : tr('Nessuna')).'</span>
                </div>
            </div>

            <div class="col-sm-4 border-right">
                <div class="description-block">
                    <a class="btn btn-info btn-block tip" data-href="'.$module->fileurl('self.php').'?id_module='.$module->id.'&resource=photo" data-toggle="modal" data-title="'.tr('Cambia foto utente').'">
                        <i class="fa fa-picture-o"></i> '.tr('Cambia foto utente').'
                    </a>
                </div>
            </div>

            <div class="col-sm-4 border-right">
                <div class="description-block">
                    <a class="btn btn-warning btn-block tip" data-href="'.$module->fileurl('self.php').'?id_module='.$module->id.'&resource=password" data-toggle="modal" data-title="'.tr('Cambia password').'">
                        <i class="fa fa-unlock-alt"></i> '.tr('Cambia password').'
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>';

echo '
<div class="row">
    <div class="col-md-6">

        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">'.tr('API').'</h3>
            </div>

            <div class="card-body">
                <p>'.tr("Puoi utilizzare il token per accedere all'API del gestionale e per visualizzare il calendario su applicazioni esterne").'.</p>

                <p>'.tr('Token personale').': <b>'.$token.'</b></p>
                <p>'.tr("URL dell'API").': <a href="'.$api.'" target="_blank">'.$api.'</a></p>

            </div>
        </div>
    </div>';

$link = $api.'&resource=sync';
echo '

    <div class="col-md-6">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">'.tr('Calendario interventi').'</h3>
            </div>

            <div class="card-body">
            <p>'.tr("Per accedere al calendario eventi attraverso l'API, accedi al seguente link").':</p>
            <a href="'.$link.'" target="_blank">'.$link.'</a>
            </div>

            <div class="card-header">
                <h3 class="card-title">'.tr('Configurazione').'</h3>
            </div>
            <div class="card-body">
            <div>
                <p>'.tr("Per _ANDROID_, scarica un'applicazione dedicata dal _LINK_", [
                    '_ANDROID_' => '<b>'.tr('Android').'</b>',
                    '_LINK_' => '<a href="https://play.google.com/store/search?q=iCalSync&c=apps" target="_blank">'.tr('Play Store').'</a>',
                ]).'.</p>

                <p>'.tr("Per _APPLE_, puoi configurare un nuovo calendario dall'app standard del calendario", [
                    '_APPLE_' => '<b>'.tr('Apple').'</b>',
                ]).'.</p>

                <p>'.tr('Per _PC_ e altri client di posta, considerare le relative funzionalità o eventuali plugin', [
                    '_PC_' => '<b>'.tr('PC').'</b>',
                ]).'.</p>
            </div>
        </div>
    </div>

</div>';

include_once App::filepath('resources\views|custom|\layout', 'bottom.php');
