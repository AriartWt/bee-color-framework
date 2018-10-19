<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/02/18
 * Time: 11:34
 */

namespace wfw\engine\core\response;

/**
 * Réponse d'un action handler suite à l'appel de handle(IAction)
 */
interface IResponse
{
    /**
     * @return mixed Données de la réponse
     */
    public function getData();
}