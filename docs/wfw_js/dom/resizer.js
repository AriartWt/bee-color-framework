/**
 * @function resizer
 * @memberOf wfw.dom
 * @param {HTMLElement} $elem Element à redimensionner
 * @param {HTMLElement} $workingNode Noeud auquel attacher les resizer-handles
 * @param {"auto"|"constrained"|"unconstrained"} [$mode] Mode de redimensionnement
 */
/**
 * Affiche les handles autour de l'élément
 * @function resizer#display
 * @memberOf wfw.dom.resizer
 */
/**
 * Masque les handles autour de l'élément
 * @function resizer#hide
 * @memberOf wfw.dom.resizer
 */
/**
 * Met à jour la poisition des handles
 * @function resizer#update
 * @memberOf wfw.dom.resizer
 */
/**
 * Enregistre un événement déclenché lorsque les handles sont affichés
 * @function resizer#onDisplay
 * @memberOf wfw.dom.resizer
 * @param {function} $fn Handler
 */
/**
 * Enregistre un événement déclenché lorsque les handles sont masqués
 * @function resizer#onHide
 * @memberOf wfw.dom.resizer
 * @param {function} $fn Handler
 */
/**
 * Enregistre un événement déclenché lorsque l'élément est redimensionné
 * @function resizer#onResize
 * @memberOf wfw.dom.resizer
 * @param {function} $fn Handler
 */
/**
 * Enregistre un événement déclenché lorsque la position d'un handle est recalculé
 * @function resizer#onHandlePosUpdate
 * @memberOf wfw.dom.resizer
 * @param {function} $fn Handler
 */
/**
 * Enregistre un événement déclenché lorsque la position de tous les handle a été recalculée
 * @function resizer#onAllHandlesPosUpdate
 * @memberOf wfw.dom.resizer
 * @param {function} $fn Handler
 */