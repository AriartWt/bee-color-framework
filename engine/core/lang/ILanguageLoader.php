<?php
namespace wfw\engine\core\lang;

/**
 * Charge un fichier de langues
 */
interface ILanguageLoader {
	/**
	 * Crée un repository à partir d'un ou plusieurs fichiers de langue, permet d'éclater les
	 * chaînes d'une même langue dans plusieurs fichier.
	 * @param string[] ...$paths Chemins d'accès aux fichiers de langue à charger.
	 *                          Les clés dupliquées doivent être écrasées dans l'ordre de
	 *                          chargement.
	 * @return IStrRepository
	 */
	public function load(string ...$paths): IStrRepository;
}