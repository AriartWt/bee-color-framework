<?php
namespace wfw\engine\package\miel\lib\helper;

use wfw\engine\lib\HTML\resources\css\ICSSManager;
use wfw\engine\lib\HTML\resources\js\IJsScriptManager;

/**
 * N'effectue aucune action
 */
final class EmptyMielHelper implements IMielHelper {
	/**
	 * Enregistre les dépendances de base pour le package miel.
	 *
	 * @param ICSSManager      $css
	 * @param IJsScriptManager $js
	 */
	public function registerDefaultDependencies(ICSSManager $css, IJsScriptManager $js): void {}

	/**
	 * @param string $key Clé à récupérer
	 * @return string attribut html à placer dans les balises.
	 */
	public function getHTMLForKey(string $key): string {
		return '';
	}
}