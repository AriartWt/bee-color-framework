<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 04/01/18
 * Time: 05:39
 */

namespace wfw\engine\core\data\model\snapshoter;

/**
 * Interface IModelSnapshoter
 *
 * @package wfw\daemons\model\snapshoter
 */
interface IModelSnapshoter
{
    /**
     * Supprime le snapshot précédent et le reconstruit de 0.
     */
    public function rebuildSnapshot():void;

    /**
     *  Met à jour le snapshot à la dernière version de chaque model
     *
     * @param string[] $modelsToRebuild (optionnel) Liste des models à reconstruire.
     */
    public function updateSnapshot(string... $modelsToRebuild):void;

    /**
     *  Retourne les models du dernir snapshot
     * @return IModel[]
     */
    public function getModels():array;
}