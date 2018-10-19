
/**
 * @typedef {object} FileExplorerActionDict
 * @property {string} url Url de l'action
 * @property {object} [paramName] Dictionnaire des noms de paramètres pour l'action AJAX.
 */
/**
 * @typedef {object} FileExplorerDict
 * @property {String} [css] Fichier css à utiliser pour les styles.
 * @property {FileExplorerActionDict} [upload] Upload un fichier. paramName disponibles : file,name
 * @property {FileExplorerActionDict} [delete] Supprimer des fichiers/dossiers.
 *                                    paramName disponibles : paths
 * @property {FileExplorerActionDict} [rename] Renomme des fichiers/dossiers.
 *                                    paramName disponibles : oldPahts, newPaths
 * @property {FileExplorerActionDict} [create] Crée un/des dossiers. paramName disponibles : paths
 * @property {FileExplorerActionDict} [list]   Récupére l'arborescence.
 */
/**
 * Offre un gestionnaire et un explorateur de fichiers/dossier efficace et simple d'utilisation
 * totalement intégré à l'interface de base.
 * Charge une seule et unique fois l'arborescence pour une navigation rapide sans délais de
 * traitements
 *  @function fileExplorer
 *  @memberOf wfw.ui
 *  @param {FileExplorerDict} params Paramètres de l'explorateur de fichiers.
 *  @property {HTMLElement} html Explorateur de fichier
 *  @property {HTMLElement[]} selected Liste des éléments séléctionnés
 */
/**
 *  Permet d'obtenir les données associées à un élément de l'explroateur de fichier à partir de
 *  l'objet HTML.
 *  @function fileExplorer#getData
 *  @param {HTMLElement} node Element dont on souhaite obtenir les données.
 */
/**
 *  Charge ou recharge les données de l'explorateur de fichier
 *  @function fileExplorer#load
 */