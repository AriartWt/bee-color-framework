<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/03/18
 * Time: 08:22
 */

namespace wfw\engine\plugin\pictureViewer;

/**
 * Element pouvant être rendu par un picture viewer
 */
interface IPictureItem
{
    /**
     * @return null|string Attribut alt
     */
    public function alt():?string;

    /**
     * @return string Chemin complet vers l'image
     */
    public function path():string;

    /**
     * @return null|string Titre associé
     */
    public function title():?string;

    /**
     * @return null|string Description
     */
    public function description():?string;
}