/**
 * @typedef Object NewsButtonsDict
 * @property {HTMLElement} [write] Bouton Nouvel article
 * @property {HTMLElement} [archive] Bouton archiver
 * @property {HTMLElement} [putOnline] Bouton mettre en ligne
 * @property {HTMLElement} [putOffline] Bouton mettre hors ligne
 *
 */
/**
 * @typedef Object NewsParamsDict
 * @property {string} [css] Fichier css pour l'affichage du module (en replacement de celui
 *                          par défaut sous engine/packages/news/webroot/Css/default.css)
 * @property {string[]} [allowedColors] Liste des couleurs disponible pour le bouton foreColor de
 *                                      l'éditeur (lteditor, module limitedForeColor)
 * @property {string} [defaultColor] Couleur par défaut dans l'éditeur (lteditor, module
 *                                   LimitedForeColor)
 * @property {NewsButtonsDict} [buttons] Liste des boutons
 */
/**
 * Module de gestion des articles.
 *  @function news
 *  @memberOf wfw.packages
 *  @param {NewsParamsDict} [$params] Paramètres du module
 *  @property {HTMLElement} html Représentation HTML du module
 *  @property {wfw.ui.table} articles Tableau dans lequel sont ajoutés les articles.
 */
/**
 * Permet de charger ou recharger la liste des articles
 *  @function news#load
 *  @memberOf wfw.packages.news
 */
/**
 * Le premier argument de l'écouteur détiendra toutes les informations de l'article sous la forme
 *  d'un tableau contenant dans l'ordre
 *  [
 *      title(string),
 *      creationDate(timestamp),
 *      online(bool),
 *      id(string),
 *      content(string),
 *      editions(array),
 *      link(string)
 *  ]
 *  @typedef Array CallbackNewsFirstArg
 */
/**
 * Listener pour les événements du module news
 * @callback NewsEventListener
 * @param {CallbackNewsFirstArg} $d Données de l'article
 */
/**
 *  Ajoute un écouteur de l'événement load.
 *  L'événement est émit chaque fois qu'un article est ajouté par le biais de la fonction load()
 *  @function news#onLoad
 *  @memberOf wfw.packages.news
 *  @param {NewsEventListener} fn Listener
 */
/**
 *  Ajoute un écouteur de l'événement edit.
 *  L'événement est émit chaque fois qu'un article est édité avec succés
 *  @function news#onEdit
 *  @memberOf wfw.packages.news
 *  @param {NewsEventListener} fn Listener
 */
 /**
 *  Ajoute un écouteur de l'événement write.
 *  L'événement est émit chaque fois qu'un article est créé avec succés
 *  @function news#onWrite
 *  @memberOf wfw.packages.news
 *  @param {NewsEventListener} fn Listener
 */
/**
 *  Ajoute un écouteur de l'événement reload.
 *  L'événement est émit chaque fois que la fonction load() est appelée après le premier appel.
 *  @function news#onReload
 *  @memberOf wfw.packages.news
 *  @param {NewsEventListener} fn Listener
 */
/**
 *  Ajoute un écouteur de l'événement archive.
 *  L'événement est émit chaque fois qu'un article est archivé avec succés
 *  @function news#onArchive
 *  @memberOf wfw.packages.news
 *  @param {NewsEventListener} fn Listener
 */
/**
 *  Ajoute un écouteur de l'événement putOnline.
 *  L'événement est émit chaque fois qu'un article est mis en ligne avec succés
 *  @function news#onPutOnline
 *  @memberOf wfw.packages.news
 *  @param {NewsEventListener} fn Listener
 */
/**
 *  Ajoute un écouteur de l'événement putOffline.
 *  L'événement est émit chaque fois qu'un article est mis hors ligne avec succés
 *  @function news#onPutOffline
 *  @memberOf wfw.packages.news
 *  @param {NewsEventListener} fn Listener
 */