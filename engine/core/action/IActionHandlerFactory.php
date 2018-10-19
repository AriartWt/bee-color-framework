<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/02/18
 * Time: 09:35
 */

namespace wfw\engine\core\action;

/**
 * Factory pour les action Handler
 */
interface IActionHandlerFactory
{
    /**
     * Créer un ActionHandler en y injectant les objets demandés au constructeur.
     * @param string $className Classe du handler. Doit implémenter IActionHandler.
     * @param array  $params    Paramètres supplémentaires à passer au handler
     * @return IActionHandler
     */
    public function create(string $className,array $params=[]):IActionHandler;
}