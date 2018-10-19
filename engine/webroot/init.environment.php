<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/12/17
 * Time: 01:20
 */

    define("START_TIME",microtime(1));

    define('DS',DIRECTORY_SEPARATOR);
    /**
     * définit une constante globale webroot permettant d'obtenir le chemin d'accès global
     * vers le répertoir engine/webroot
     */
    define('WEBROOT', dirname(__FILE__));
    define("ENGINE",dirname(WEBROOT));
    define("ROOT",dirname(ENGINE));/**<  Chemin d'accés au dossier website (racine du projet) */
    define("SITE",ROOT.DS."site");/**<  Chemin d'accés au dossier website/site */
    define("DAEMONS",ROOT.DS."daemons");/**<  Chemin d'accés au dossier website/daemons */
    define("CLI",ROOT.DS."cli");
    define("WWW",dirname(ROOT));/**<  obtient le chemin d'accés au dossier HTTPD/WWW */
    define('CORE', ENGINE.DS.'core');/**<  obtient le chemin d'accés vers le dossier egine/core */
    /**  défini le chemin d'accé au fichier website */
    define('BASE_URL',
        ((dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))!=="\\")
            ?dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])))
            :false)
    );
    define('PLUGINS',ENGINE.DS.'plugin'.DS);/**  Chemin d'accés au fichier website/engine/plugin */

    mb_internal_encoding('UTF-8');
    ignore_user_abort(true);//N'interromp pas l'execution à la deconnexion du client

    setlocale(LC_CTYPE, 'fr_FR','fra');
    date_default_timezone_set('Europe/Paris');

    require CORE.DS.'Autoloader.php';
    use wfw\Autoloader;
    Autoloader::register();

    use wfw\engine\lib\debug\Debuger;
    function debug($var){
        Debuger::get()->debug($var);
    }