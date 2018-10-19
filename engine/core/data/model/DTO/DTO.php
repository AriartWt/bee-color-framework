<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 12/12/17
 * Time: 05:13
 */

namespace wfw\engine\core\data\model\DTO;

use wfw\engine\lib\data\string\json\IJSONPrintInfos;
use wfw\engine\lib\PHP\types\UUID;

/**
 *  Data Transfert Object
 */
class DTO implements IDTO,IJSONPrintInfos
{
    /** @var UUID $_id */
    private $_id;

    /**
     * DTO constructor.
     *
     * @param UUID $id
     */
    public function __construct(UUID $id)
    {
        $this->_id = $id;
    }

    /**
     * @return UUID
     */
    public function getId():UUID
    {
        return $this->_id;
    }

	/**
	 * @return array string[](property names) : Liste des propriétés
	 *               à ne pas conserver.
	 */
	public function skipProperties(): array {
		return [];
	}

	/**
	 * @return array property => callable : Pour chaque objet, une liste de propriétés
	 *               dont chaque callable est une fonction qui prend pour argument la valeur de la propriété.
	 */
	public function transformProperties(): array {
		return [
			"_id" => function(UUID $id){
				return (string) $id;
			}
		];
	}

	/**
	 * @return array Propriété -> valeur/callable : Pour chaque propriété, un callable ou une valuer.
	 *               Si callable : accepte en argument l'objet lui même.
	 */
	public function addProperties(): array {
		return [];
	}
}