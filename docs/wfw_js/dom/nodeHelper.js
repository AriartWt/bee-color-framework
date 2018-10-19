
/**
 * Attache chaque noeud $childs à $parent
 * @function appendTo
 * @memberOf wfw.dom
 * @param {HTMLElement} $parent Element parent auquel attacher les enfants
 * @param {...HTMLElement} $childs Liste des enfants à attacher.
 * @return {HTMLElement} L'élement parent.
 */
/**
 * Attache les elements en cascade
 * @function appendCascade
 * @memberOf wfw.dom
 * @param {...HTMLElement} $nodes Liste des noeuds à imbriquer.
 * @return {HTMLElement} L'élement parent.
 * @exemple appendCascade(ul,li,p,a) ; ul>li>p>a
 */
/**
 * Permet de créer un element à l'aide de document.createElement
 * @function create
 * @memberOf wfw.dom
 * @param {string} $name Nom de la balise html
 * @param {Object} [$params] liste de couples clé/valeurs permettant d'intialiser l'objet
 * @return {HTMLElement} L'élement créé
 */
/**
 * Insert un noeud juste après un autre dans la liste des enfants de son parent
 * @function insertAfter
 * @memberOf wfw.dom
 * @param {HTMLElement} $node Noeud à ajouter
 * @param {HTMLElement} $ref  Element de référence
 */
/**
 * @namespace import
 * @memberOf wfw.dom
 */
/**
 * Importe un svg
 * @function svg
 * @memberOf wfw.dom.import
 * @param {string} $url Lien vers le svg à importer
 */