<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 25/05/18
 * Time: 17:21
 */

namespace wfw\engine\lib\data\string\json;

/**
 * Permet d'encoder des objets au format JSON en important toutes leurs propriétés (private, public, protected)
 */
interface IJSONEncoder{
	/**
	 * @param mixed $data                Données à encoder au format JSON
	 * @param array $skipProperties      Propriétées à omettre sous la forme class=>string[] properties
	 * @param array $transformProperties Callback de transformation d'une propriété sous la forme
	 *                                   class=>[propName=>callable/value]. Le résultat de callable sera
	 *                                   utilisée comme valeur de propriété
	 * @param array $addProperties       Propriétées à ajouter sous la forme class=>[ property => callable/value ]
	 *                                   Le callable doit etre une fonction prenant l'objet en paramètre.
	 * @param int   $opts                Options json_encode
	 * @return string
	 */
	public function jsonEncode(
		$data,
		array $skipProperties=[],
		array $transformProperties=[],
		array $addProperties=[],
		int $opts = 0
	):string;
}