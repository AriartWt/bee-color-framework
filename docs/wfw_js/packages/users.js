/**
 * @typedef {object} UserButtonsDict
 * @memberOf wfw.packages.users
 * @property {HTMLElement} [register] Bouton nouvel utilisateur
 * @property {HTMLElement} [remove] Bouton supprimer des utilisateurs
 * @property {HTMLElement} [enable] Bouton activer des utilisateurs
 * @property {HTMLElement} [disable] Bouton désactiver des utilisateurs
 */
/**
 * @typedef {object} UsersParamDict
 * @memberOf wfw.packages.users
 * @property {UserButtonsDict} [buttons] Boutons d'action du module
 * @property {string} [css] Lien vers le fichier css des styles du module (en replacement de celui
 *                          par défaut sous engine/packages/users/webroot/Css/default.css)
 * @property {boolean} [disableClientLogin] Si true : supprime les règles interdisant à un client
 *                                          d'avoir un login dissocié de son adresse email et traite
 *                                          les clients comme des utilisateurs normaux.
 */
/**
 * Module de gestion des utilisateurs
 *  @class users
 *  @memberOf wfw.packages
 *  @params {UsersParamDict} [$params] Paramètres du module
 *  @property {HTMLElement} html Représentation HTML du module
 *  @property {wfw.ui.table} users Tableau contenant les utilisateurs
 */
/**
 * Permet de charger ou recharger la liste des utilisateurs
 * @method load
 * @instance
 * @memberOf wfw.packages.users
 */
/**
 * Le premier argument de l'écouter contiendra toutes le sinformations relative à l'utilisateur
 * sous la forme d'un tableau :
 * [
 *      login(string),
 *      email(string),
 *      state(string),
 *      type(string),
 *      id(string)
 *      settings(Object)
 * ]
 * @typedef {array} CallbackUsersFirstArg
 * @memberOf wfw.packages.users
 */
/**
 * Listener d'événements users
 * @callback {function} UsersEventListener
 * @memberOf wfw.packages.users
 * @param {CallbackUsersFirstArg} $d Données de l'utilisateur concerné par l'événement
 */
/**
 * Ajoute un écouteur de l'événement load.
 *  L'événement est émit chaque fois qu'un utilisateur est ajouté par le biais de la fonction load()
 *  @method onLoad
 *  @memberOf wfw.packages.users
 *  @instance
 *  @param {UsersEventListener} $fn Listener
 */
/**
 *  Ajoute un écouteur de l'événement reload.
 *  L'événement est émit chaque fois que la fonction load() est appelée après le premier appel.
 *  @method onReload
 *  @instance
 *  @memberOf wfw.packages.users
 *  @param {UsersEventListener} fn Listener
 */
/**
 * Ajoute un écouteur de l'événement remove.
 *  L'événement est émit chaque fois qu'un utilisateur est supprimé avec succés
 *  @method onRemove
 *  @instance
 *  @memberOf wfw.packages.users
 *  @param {UsersEventListener} $fn Listener
 */
/**
 * Ajoute un écouteur de l'événement enable.
 *  L'événement est émit chaque fois qu'un utilisateur est activé avec succés
 *  @method onEnable
 *  @instance
 *  @memberOf wfw.packages.users
 *  @param {UsersEventListener} $fn Listener
 */
/**
 * Ajoute un écouteur de l'événement disable.
 *  L'événement est émit chaque fois qu'un utilisateur est désactivé avec succés
 *  @method onDisable
 *  @instance
 *  @memberOf wfw.packages.users
 *  @param {UsersEventListener} $fn Listener
 */
/**
 * Ajoute un écouteur de l'événement register.
 *  L'événement est émit chaque fois qu'un utilisateur est créé avec succés
 *  @method onRegister
 *  @instance
 *  @memberOf wfw.packages.users
 *  @param {UsersEventListener} $fn Listener
 */
/**
 * Ajoute un écouteur de l'événement mailChange.
 *  L'événement est émit chaque fois qu'un email d'un utilisateur est modifié avec succés
 *  @mthod onMailChange
 *  @instance
 *  @memberOf wfw.packages.users
 *  @param {UsersEventListener} $fn Listener
 */
/**
 * Ajoute un écouteur de l'événement loginChange.
 *  L'événement est émit chaque fois qu'un login d'un utilisateur est modifié avec succés
 *  @mthod onLoginChange
 *  @instance
 *  @memberOf wfw.packages.users
 *  @param {UsersEventListener} $fn Listener
 */
/**
 * Ajoute un écouteur de l'événement passwordReset.
 *  L'événement est émit chaque fois qu'un mot de passe d'utilisateur est réinitialisé avec succés
 *  @mthod onPasswordReset
 *  @instance
 *  @memberOf wfw.packages.users
 *  @param {UsersEventListener} $fn Listener
 */
/**
 * Ajoute un écouteur de l'événement cancelChangeMail.
 *  L'événement est émit chaque fois qu'une demande de changement de mail d'utilisateur est annulée avec succés
 *  @mthod onCancelChangeMail
 *  @instance
 *  @memberOf wfw.packages.users
 *  @param {UsersEventListener} $fn Listener
 */
/**
 * Ajoute un écouteur de l'événement cancelRegistration.
 *  L'événement est émit chaque fois qu'une demande d'inscription d'utilisateur est annulée avec succés
 *  @method onCancelRegistration
 *  @instance
 *  @memberOf wfw.packages.users
 *  @param {UsersEventListener} $fn Listener
 */
/**
 * Ajoute un écouteur de l'événement cancelPasswordReset.
 *  L'événement est émit chaque fois qu'une demande de réinitialisation d'un utilisateur est annulée avec succés
 *  @method onCancelPasswordReset
 *  @instance
 *  @memberOf wfw.packages.users
 *  @param {UsersEventListener} $fn Listener
 */