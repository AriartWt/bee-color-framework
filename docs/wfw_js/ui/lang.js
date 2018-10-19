/**
 * @namespace lang
 * @memberOf wfw.ui
 */
/**
 * Obtient une chaine de caractères décrite par $path et effectue les remplacements s'il y en,
 * et qu'ils sont tous précisés dans $replaces. Chaque remplacement consomme un élément de
 * $replaces dans l'ordre jusqu'à ce qu'il n'y en ait plus, ou qu'il n'y ait plus de
 * remplacement disponible.
 * @function get
 * @memberOf wfw.ui.lang
 * @param {string}   $path     Chemin d'accés à la chaine
 * @param {...string} $replaces Remplacements à effectuer
 */
/**
 * Charge un pack de langue en effectuant une requête ajax par le biais de wfwAPI sur
 * lang/translationsRepository
 * @function load
 * @memberOf wfw.ui.lang
 * @param {string} $path Chemin du pack de langue
 * @param {Function} [$then] Optionnal. Fonction a executer une fois la langue chargée.
 */