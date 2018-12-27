/**
 * Gestionnaire de sources et de nasmespace de l'api js WFW.
 * L'objet résultant est à la fois un objet et un namespace.
 * Déclare automatiquement une instance appelée wfw.
 * @class WFW
 */
/**
 * @external wfw
 * @type WFW
 */
/**
 * Enregistre une librairie sous le namespace $namespace. Une librairie peut-être n'importe quoi
 * @method define
 * @instance
 * @memberOf WFW
 * @param {string}  $namespace Chemin vers la librairie dans wfw
 * @param {*}       $o         Valeur à inscrire sous le chemin $path dans wfw
 * @param {boolean} [$ovrd]      Si true : autorise une autre librairie à écraser celle là.
 */
/**
 * Ajoute une fonction à executer une fois toutes les dépendances chargées.
 * @method ready
 * @instance
 * @memberOf WFW
 * @param {Function} $fn Fonction à executer
 * @param {boolean} [$first] Placée dans la liste des fonctions executées en priorité. A
 *                           utiliser surtout pour l'initialisation des librairies.
 */
/**
 * Demande le chargement d'un fichier JavaScript.
 * @method require
 * @instance
 * @memberOf WFW
 * @param {...string} $libs Liste des fichiers à charger. Par défaut, la résolution se fait à
 *                          partir de $webroot. Pour un chargement absolu, mettre @ devant l'url:
 *                          local : "api/settings"
 *                          distant : "@http://domain.com/js/malib.js"
 */
/**
 * Permet de savoir si la librairie interne $path est définie.
 * @method defined
 * @instance
 * @memberOf WFW
 * @param {string} $path Librairie à tester
 * @returns {boolean} True si la librairie est définie, false sinon.
 */
/**
 * Ajoute une fonction à executer juste avant que toutes les fonctions wfw.ready soit appelées.
 * Permet notament d'executer des commandes AJAX importantes avant le démarrage.
 * Toutes les fonctions init() doivent appeler wfw.next() une fois que leur travail d'initialisation
 * est terminé !
 * @method init
 * @instance
 * @memberOf WFW
 * @param {Function} $fn Fonction à executer
 */
/**
 * Permet d'appeler la fonction d'initialisation asynchrone suivante.
 * @method next
 * @instance
 * @memberOf WFW
 */
/**
 * Permet de formater une url locale en ajoutant wfw.webroot devant et un cache burst derrière
 * définis dans app_infos sous sal clé "app/cache_burst".
 * @method url
 * @instance
 * @memberOf WFW
 * @param {string} $url Si true, n'ajoute pas le cache_burst à la fin de l'url.
 * @param {boolean} [$cache] Si true, n'ajoute pas le cache_burst à la fin de l'url.
 */