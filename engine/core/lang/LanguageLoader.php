<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 21/02/18
 * Time: 08:00
 */

namespace wfw\engine\core\lang;

use stdClass;
use wfw\engine\lib\PHP\objects\StdClassOperator;
use wfw\engine\lib\PHP\system\filesystem\json\JSONFile;

/**
 * Charge des fichiers de langue au format JSON
 */
final class LanguageLoader implements ILanguageLoader
{
    /**
     * @param string[] $paths Chemin d'accès au fichier de langue à charger.
     * @return IStrRepository
     * @throws \Exception
     */
    public function load(string ...$paths): IStrRepository
    {   //TODO : ajouter un cache pour les fichiers ?
        $data = new stdClass();
        $operator = new StdClassOperator($data);
        foreach($paths as $path){
            $operator->mergeStdClass((new JSONFile($path))->read());
        }
        return new StrRepository($data);
    }
}