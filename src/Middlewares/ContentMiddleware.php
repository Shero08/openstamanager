<?php

namespace Middlewares;

use Models\Module;
use Modules;
use Update;
use Util\Query;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Classe per l'impostazione automatica delle variabili rilevanti per il funzionamento del progetto.
 *
 * @since 2.5
 */
class ContentMiddleware extends Middleware
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $route = $request->getAttribute('route');
        if (!$route || !$this->database->isConnected() || Update::isUpdateAvailable()) {
            return $next($request, $response);
        }

        $args = $route->getArguments();

        Module::setCurrent($args['module_id']);
        Query::setModuleRecord($args['reference_id']);

        // Variabili fondamentali
        $module = Module::getCurrent();

        $id_module = $module['id'];
        $args['id_module'] = $id_module;
        $args['id_record'] = $args['record_id'];

        $args['structure'] = $module;
        $args['module'] = $module;

        $user = auth()->getUser();
        $args['user'] = $user;

        $this->addVariable('order_manager_id', $this->database->isInstalled() ? Modules::get('Stato dei serivizi')['id'] : null);
        $this->addVariable('is_mobile', isMobile());

        // Versione
        $this->addVariable('version', \Update::getVersion());
        $this->addVariable('revision', \Update::getRevision());

        // Richiesta AJAX
        $this->addVariable('handle_ajax', $request->isXhr() && filter('ajax'));

        // Argomenti di ricerca dalla sessione
        $search = [];
        $array = $_SESSION['module_'.$id_module];
        if (!empty($array)) {
            foreach ($array as $field => $value) {
                if (!empty($value) && starts_with($field, 'search_')) {
                    $field_name = str_replace('search_', '', $field);

                    $search[$field_name] = $value;
                }
            }
        }
        $this->addVariable('search', $search);

        // Menu principale
        $this->addVariable('main_menu', Modules::getMainMenu());

        // Impostazione degli argomenti
        $request = $this->setArgs($request, $args);

        return $next($request, $response);
    }
}