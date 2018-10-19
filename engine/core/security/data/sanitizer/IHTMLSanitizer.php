<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/05/18
 * Time: 14:27
 */

namespace wfw\engine\core\security\data\sanitizer;

/**
 * Parse et assaini un chaine contenant de l'HTML (but principal : supprimer les failles xss)
 */
interface IHTMLSanitizer
{
    /**
     * @param string $html à purifier
     * @return string html purifié
     */
    public function sanitizeHTML(string $html):string;
}