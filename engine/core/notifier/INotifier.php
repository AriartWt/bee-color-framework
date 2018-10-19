<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 19/02/18
 * Time: 07:39
 */

namespace wfw\engine\core\notifier;

/**
 * Permet d'afficher des messages à l'utilisateur.
 */
interface INotifier
{
    /**
     * Ajoute un message
     *
     * @param IMessage $message Message à ajouter
     */
    public function addMessage(IMessage $message):void;

    /**
     * Consomme un message. Premier arrivé, premier servi.
     * @return null|string Représentation du message. Null s'il n'y en a pas.
     */
    public function print():?string;

    /**
     * Consomme tous les messages.
     * @return null|string Représentation des messages (ordre premier arrivé, premier affiché). Null
     *                     s'il n'y en a pas.
     */
    public function printAll():?string;

    /**
     * Remet à zéro la liste des messages du notifier et renvoie l'ancien tableau de messages
     * @return array
     */
    public function reset():array;
}