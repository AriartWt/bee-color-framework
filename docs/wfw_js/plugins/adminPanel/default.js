/**
 * @typedef Object ModuleBtnDict
 * @property {string} [title] Titre à afficher sur le bouton au survol
 * @property {string|HTMLElement} [icon] Icone à afficher dans le panel pour le module
 * @property {string} [panelTitle] Titre du module à afficher dans la fenêtre ouverte au clique sur
 *                                 le bouton.
 */
/**
 * Il est possible d'omettre les paramètres [btn] pour les modules de base, ou de les préciser
 * pour écraser les paramètres de base.
 * @typedef Object ModuleParams
 * @property {Object} [params] Paramètres à transmettre directement au module via son constructeur.
 * @property {ModuleBtnDict} [btn] Paramètres du bouton du module visible dans le panel
 * @property {boolean} [autoload] Si true : le module est chargé automatiquement, ignorant le
 *                                loadOrder. Si le module est redéfini dans le loadOrder, la fonction
 *                                load sera appelée une seconde fois.
 */
/**
 * @typedef Object LogoutParams
 * @property {string} [url] Url de déconnexion
 * @property {string} [title] Titre affiché à l'utilisateur au survol du bouton à la souris.
 * @property {string} [confirm] Message à afficher dans la boite de confirmation
 * @property {string} [icon] Lien vers l'icone à afficher (format svg obligatoire)
 * @property {string} [ok] COntenu du bouton OK de la boite de confirmation
 * @property {string} [cancel] Contenu du bouton CANCEL de la boite de confirmation
 */
/**
 * Crée automatiquement un panel de gestion en fonction des paramètres passés.
 * Chacun des modules doit être défini dans wfw.packages.[module_name]
 * Chacun des modules peut disposer d'une fonction load() qui sera appelée une fois le gestionnaire
 * de pannel créé, juste avant que les appels aux fonction enregistrées par onready() soient effectués.
 * Chacun des modules peut disposer d'une fonction hide() et/ou show() qui sera appelée à chaque
 * fois que la fenêtre d'un module sera affiché/masqué à l'écran.
 * Le 'module' spécial logout permet de créer un bouton de déconnexion, il est personnalisable
 * avec des options bien particulière définies par le type LogoutParams.
 * Les modules chargés doivent avoir une propriété html dans laquelle se trouve la représentation
 * html complète de l'espace de travail du module.
 * @function default
 * @memberOf wfw.plugins.adminPanel
 * @param {Object.<string,Object.<string,ModuleParams|LogoutParams>>} $panels Liste des panels à créer sous la forme name=>{}
 * @param {String[]} $loadOrder Ordre d'appel des fonctions load() de chacun des modules à charger.
 * @property {boolean} loaded True une fois les modules créé et les fonctions load() appelées,
 *                            false avant. A noter que si les fonction load() sont asynchrones,
 *                            les modules ne seront pas fini de charger lorsque ce booleen sera à true,
 *                            mais ils seront totalement initialisés.
 * */
/**
 * Permet d'enregistrer un callback qui sera appelé une fois tous les modules initialisés.
 * @function default#onready
 * @memberOf wfw.plugins.adminPanel.default
 * @param {Function} $fn Fonction à executer une fois les module sinitialisés.
 */
/**
 *  Retourne un module initialisé par le gestionnaire de modules.
 *  @function default#getModule
 *  @memberOf wfw.plugins.adminPanel.default
 *  @param {string} $moduleName Retourne le module de nom $name
 *  @return Object
 */