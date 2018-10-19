<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/12/17
 * Time: 03:47
 */

namespace wfw\engine\core\conf\io\adapters;


use stdClass;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\conf\io\IConfIOAdapter;
use wfw\engine\lib\PHP\errors\IllegalInvocation;

/**
 *  Aucune sauvegarde, aucune lecture.
 */
class NoneConfIOAdapter implements IConfIOAdapter
{

    /**
     *  Parse un fichier de configuration et retourne un objet stdClass utilisable par la classe Conf
     *
     * @param string $path Chemin d'accès au fichier (sans l'extension)
     *
     * @return stdClass
     */
    public function parse(string $path): stdClass
    {
        return new stdClass();
    }

    /**
     *     Enregistre un fichier de configuration
     *
     * @param IConf $conf Objet de configuration à sauvegarder
     *
     * @internal param string $path Chemin de sauvegarde du fichier de conf (sans extension)
     */
    public function save(IConf $conf): void
    {
        throw new IllegalInvocation(self::class." cannot be saved !");
    }
}