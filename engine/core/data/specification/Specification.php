<?php
namespace wfw\engine\core\data\specification;

/**
 *  Spécification de base
 */
abstract class Specification implements ISpecification {
	/**
	 * @return string (représentation hexadécimale de la serialisation de l'instance courante)
	 */
	public final function __toString() {
		return unpack("H*",serialize($this))[1];
	}
}