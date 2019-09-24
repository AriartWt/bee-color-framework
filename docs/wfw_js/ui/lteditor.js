/**
 * @typedef {object} lteditorButtonDict
 * @memberOf wfw.ui.lteditor
 * @property {function} action (event,btn,editor) Fonction executée au clique sur le bouton
 * @property {HTMLElement} icon Icone à afficher
 * @property {function} [init] Fonction appelée lorsque l'éditeur est initialisé (btn,editor)
 * @property {function} [state] Fonction appelée pour déterminer l'état du bouton
 * @property {Object} [params] Paramètres de création passé à wfw.dom.create lors de la création de
 *                             l'element parent de icon
 */
/**
 * @typedef {object} lteditorOptions
 * @memberOf wfw.ui.lteditor
 * @property {object} [main] Options de création de l'element html englobant (.lteditor)
 * @property {object} [textarea] Options de création de l'élément html textarea contenant la valeur
 *                               innerHTML du .lteditor-content
 * @property {object} [actions] Options de création de la barre d'actions
 * @property {object} [action] Options de création des actions
 * @property {string} [selectedAction] classe ajoutée à un élément séléctionné
 * @property {object} [body] Options de création du body
 * @property {object} [content] Options de création du content
 */
/**
 * @typedef {object} lteditorParamsDict
 * @memberOf wfw.ui.lteditor
 * @property {string} [defaultParagraphSeparator] Balise insérée sur la touche entrée
 * @property {string} [css] Lien du fichier css à charger pour l'apparence de l'éditeur.
 * @property {boolean} [disableAutoCss] Si true, désactive le chargement de la feuille de style
 * @property {lteditorOptions} [opts] Options de créations des différents elements HTML
 */
/**
 * @class lteditor
 * @memberOf wfw.ui
 * @property {HTMLElement} html Editeur complet
 * @property {HTMLElement} content Element contentEditable
 * @property {HTMLElement} body Corps de l'editeur, contenant content
 * @property {HTMLElement} head Contener ayant les boutons
 * @property {string} value Contenu (innerHTML) de l'element content
 * @property {lteditorParamsDict} options Options de l'éditeur
 */
/**
 * Ajoute un événement qui écoute les changements apportés au contenu de l'éditeur
 * @method onChange
 * @instance
 * @memberOf wfw.ui.lteditor
 * @param {function} $fn Handler de l'événement onChange sur la valeur de l'éditeur
 */
/**
 * Ajoute un nouveau bouton à la barre d'actions de l'éditeur.
 * @method addButton
 * @instance
 * @memberOf wfw.ui.lteditor
 * @param {string} $name Nom du bouton
 * @param {lteditorButtonDict} $btn Bouton
 * @param {string} [$before] Nom du bouton avant lequel insérer le bouton courant
 */