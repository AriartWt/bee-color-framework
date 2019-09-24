<?php
namespace wfw\engine\core\data\model;

/**
 * Model sorter de base
 */
abstract class AbstractModelSorter implements IArraySorter {
	/**
	 * @return string (représentation hexadécimale de la serialisation de l'instance courante)
	 */
	public final function __toString() {
		return unpack("H*",serialize($this))[1];
	}
}