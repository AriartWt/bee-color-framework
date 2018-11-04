<?php
namespace wfw\engine\core\lang;

/**
 * Permet d'effectuer une traduction sur des chaines de caractères.
 */
interface ITranslator extends IStrRepository {
	/**
	 * @return string Langue par défaut.
	 */
	public function getCurrentLanguage():string;

	/**
	 * @param string $lang Nouvelle langue par défaut.
	 */
	public function changeCurrentLanguage(string $lang):void;

	/**
	 * @param string      $key  Clé d'obtention
	 * @param null|string $lang Langue souhaitée
	 * @return string
	 */
	public function getAndTranslate(string $key, ?string $lang=null):string;

	/**
	 * @param string $key  Clé d'obtention
	 * @param string $lang Langue souhaitée
	 * @return \stdClass Ensemble de traductions
	 */
	public function getAllTranslations(string $key, ?string $lang=null):\stdClass;

	/**
	 * @param string      $key         Clé d'obtention.
	 * @param null|string $lang        Langue souhaitée.
	 * @param string[]    ...$replaces Liste des remplacements
	 * @return string Chaine tranduite dont les remplacements ont été effectués.
	 */
	public function getTranslateAndReplace(
		string $key,
		?string $lang=null,
		string ...$replaces
	):string;
}