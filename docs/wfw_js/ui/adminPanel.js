/**
 * @namespace adminPanel
 * @memberOf wfw.ui
 * @property {HTMLElement} css Elément html link contenant le lien vers le fichier css principal
 * @property {HTMLElement} icon Bouton princpal permettant d'afficher/cacher le panel
 */
/**
 *  Retourne un composant du panel d'administration
 *  @function get
 *  @memberOf wfw.ui.adminPanel
 *  @param {string} Nom du panel
 *  @throws {Error} Si le panel n'existe pas
 *  @return {HTMLElement}
 */
/**
 *  Ajoute un composant au panel d'administration
 *  @function add
 *  @memberOf wfw.ui.adminPanel
 *  @param {string} Nom du panel
 *  @param {Object} Paramètres de création ( voir wfw.dom.create )
 *  @throws {Error} Si le panel existe
 */
/**
 *  Supprime un composant du panel d'administration
 *  @function remove
 *  @memberOf wfw.ui.adminPanel
 *  @param {string} Nom du panel
 *  @throws {Error} Si le panel n'existe pas
 */
/**
 *  Teste l'existence d'un panel
 *  @function exists
 *  @memberOf wfw.ui.adminPanel
 *  @param {string} Nom du panel à tester
 *  @return {boolean} True si le panel existe, false sinon
 */
/**
 *  Ajoute un listener à l'événement onshow qui survient lorsque le panel est affiché
 *  @function onshow
 *  @memberOf wfw.ui.adminPanel
 *  @param {Function} Listener
 */
/**
 *  Ajoute un listener à l'événement onshow qui survient lorsque le panel est masqué
 *  @function onhide
 *  @memberOf wfw.ui.adminPanel
 *  @param {Function} Listener
 */
/**
 *  Ajoute un listener à l'événement onready qui survient lorsque le panel est entièrement chargé
 *  @function onready
 *  @memberOf wfw.ui.adminPanel
 *  @param {Function} Listener
 */
/**
 *  Ordonne à l'iframe de se recharger.
 *  @function framerefresh
 *  @memberOf wfw.ui.adminPanel
 */
/**
 *  Ajoute un listener à l'évenement onframeload qui survient à chaque chargement de l'iframe.
 *  @function onframeload
 *  @memberOf wfw.ui.adminPanel
 *  @param {Function} Listener
 */
/**
 *  Retourne la frame contenant la page du site.
 *  @function frame
 *  @memberOf wfw.ui.adminPanel
 *  @param {boolean} [$doc=false] Si true, retourne le document de la frame, la frame sinon
 */
/**
 *  Cache le panel
 *  @function hide
 *  @memberOf wfw.ui.adminPanel
 */
/**
 *  Affiche le panel
 *  @function show
 *  @memberOf wfw.ui.adminPanel
 */
/**
 *  Crée un bouton à ajouter à l'un des panels.
 *  @function createButton
 *  @memberOf wfw.ui.adminPanel
 *  @param {string} $name Nom du bouton
 *  @param {string} $title Titre affiché au survol du bouton
 *  @param {string} $svg Url vers un svg à importer
 *  @param {string} [$panelTitle] Si précisé, crée automatiquement un mainPanel avec ce titre.
 */
/**
 *  Crée un panel de type main, qui s'affiche par dessus l'iframe et contient les actions que
 *  l'utilisateur peut effectuer pour un bouton particulier.
 *  @function createMainPanel
 *  @memberOf wfw.ui.adminPanel
 *  @param {string} name Nom du bouton associé
 *  @param {string} title Titre du panel.
 */
/**
 *  Créer un bouton de deconnexion
 *  @function createLogout
 *  @memberOf wfw.ui.adminPanel
 *  @param {string} $url Url pour la déconnexion
 *  @param {string} $title Titre du bouton
 *  @param {string} $dialog Dialog affiché dans la boite
 *  @param {string} [$icon=wfw.webroot+"/Image/svg/icons/power-button-off.svg"] Url vers l'icone
 *  @param {string} [$ok="Oui"] Contenu du bouton validant la deconnexion
 *  @param {string} [$cancel="Non"] Contenu du bouton annulant la deconnexion
 */
/**
 * Permet de savoir si le panel est chargé
 * @function loaded
 * @memberOf wfw.ui.adminPanel
 */