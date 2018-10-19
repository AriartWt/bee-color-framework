<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/03/18
 * Time: 07:13
 */

namespace wfw\engine\plugin\pictureViewer;

use \wfw\engine\core\view\IView;

/**
 * Permet d'afficher un slide d'images.
 */
interface IPictureViewer extends IView{
    /**
     * @return IPictureViewerOptions Options de création du slider
     */
    public function getOptions():IPictureViewerOptions;
}