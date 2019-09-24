
/**
 * Imite et personnalise le fonction d'un objet select natif, en ajoutant plusieurs fonctionnalités.
 * Gère notament le groupage d'options (sans limitation de profondeur), il est possible de
 * séléctionner des groupes ou leur contenu, de permettre la selection d'une à plusieurs valeurs,
 * d'effectuer des recherche sur les options disponibles.
 * Le select garantie que les données associées à chaque group et options sont uniques (par référence
 * pour les type Array et Object, par valeur pour les autres.
 * @class select
 * @memberOf wfw.dom
 * @param {SelectParams} $params Paramètres de création du select
 * @property {SelectElement} html Code HTML du select
 * @property {boolean} isOpen Permet de savoir si le select est actuellement ouvert
 * @property {Array} value Valeur du select
 */
/**
 * @typedef HTMLElement SelectElement
 * @memberOf wfw.dom.select
 * @property {wfw.dom.select} wfw Instance du select
 * @property {Array} value Valeur du select
 */
/**
 * Permet de trier un select lorsque l'utilisateur y effectue une recherche
 * @callback SortSelect
 * @memberOf wfw.dom.select
 * @param {SortObj} [$a] Objet de comparaison
 * @param {SortObj} [$b] Objet à comparer
 * @return {int} -1 si $a < $b; 0 si $a === $b; 1 sinon
 */
/**
 * @typedef object SortObj
 * @memberOf wfw.dom.select
 * @property {string} value Valeur textuelle résultant de la recherche
 * @property {int} pos Index auquel la correspance de la recherche commence dans value
 * @property {HTMLElement} node Noeud correspondant
 * @property {HTMLElement} textNode Noeud devant recevoir value
 */
/**
 * Utilisé a chaque affichage d'une option ou d'un groupe dans le panneau d'affichage des options
 * séléctionnées.
 * @callback ValueCallback
 * @param {string} $txt Texte à afficher
 * @param {*} $data Données associées à l'élément
 * @return HTMLElement Element qui sera inséré dans le champ value
 */
/**
 * Permet d'indiquer à l'objet select le noeud dans lequel se trouve le texte pour les recherches
 * de l'utilisateur
 * @callback TxtNodeCallback
 * @memberOf wfw.dom.select
 * @param {HTMLElement} $noeud Noeud
 */
/**
 * Utilisé à la construction des options
 * @callback CreateOptCallback
 * @memberOf wfw.dom.select
 * @param {object} $p Paramètres de création de l'option.
 * @param {TxtNodeCallback} $txtNode Callback permettant d'indiquer à l'objet select le noeud dans
 *                                   lequel le texte à utiliser pour les recherche est inséré.
 * @return HTMLElement Element qui sera inséré dans l'option
 */
/**
 * Utilisé à la construction des groupes
 * @callback CreateGroupCallback
 * @memberOf wfw.dom.select
 * @param {object} $p Paramètres de création du groupe.
 * @param {TxtNodeCallback} $txtNode Callback permettant d'indiquer à l'objet select le noeud dans
 *                                   lequel le texte à utiliser pour les recherche est inséré.
 * @return HTMLElement Element qui sera inséré dans le groupe
 */
/**
 * @typedef object DisplayerParams
 * @memberOf wfw.dom.select
 * @property {ValueCallback} [value] Permet de mettre en forme l'affichage de l'option dans l'espace
 *                                   valeur du select lorsqu'elle est selectionnée par l'utilisateur.
 * @property {CreateOptCallback} [opt] Permet de gérer l'affichage de l'option dans la liste
 * @property {CreateGroupCallback} [group] Permet de gérer l'affichage des groupes dans la liste
 */
/**
 * @typedef object SelectOption
 * @memberOf wfw.dom.select
 * @property {string} [name] Nom affiché de l'option (pour les DisplayerParams par défaut)
 * @property {*} value Valeur associée
 */
/**
 * @typedef object SelectGroup
 * @memberOf wfw.dom.select
 * @property {string} [name] Nom affiché du groupe (pour les DisplayerParams par défaut)
 * @property {*} [value] Valeur associées
 * @property {SelectGroup[]} [groups] Groupes associés
 * @property {SelectOption[]} [opts] Options associées
 */
/**
 * @typedef object SelectParams
 * @memberOf wfw.dom.select
 * @property {boolean} [allowReset] Ajoute automatiquement une option en première position pour
 *                                  remettre à zéro la valeur du select.
 * @property {boolean} [multiSelect] Active la selection de plusieurs options.
 * @property {boolean} [allowGroupSelection] Permet à l'utilisateur de séléctionner un groupe
 * @property {boolean} [selectGroupItems] Permet d'insérer dans la liste des résultat toutes les
 *                                        options contenue dans un groupe et ses sous groupes.
 *                                        Seules les options visuellement disponible sont ajoutées.
 * @property {boolean} [disableCloseOnChange] Permet de désactiver la fermeture du select à chaque
 *                                            choix de l'utilisateur lorsque multiSelect est à false.
 * @property {string} [placeholder] Texte affiché lorsqu'aucune option n'est séléctionnée.
 * @property {string} [css] Fichier CSS à charger à la place du fichier par défaut sous
 *                          engine/webroot/Css/api/dom/select.css
 * @property {SortSelect} [sortSearch] fonction de tri des résultat matchant la recherche de
 *                                     l'utilisateur
 * @property {SelectOption[]} [opts] Liste des options sans groupes.
 * @property {SelectGroup[]} [groups] Liste des groupes.
 */
/**
 * Ouvre le select
 * @method open
 * @instance
 * @memberOf wfw.dom.select
 */
/**
 * Ferme le select
 * @method close
 * @instance
 * @memberOf wfw.dom.select
 */
/**
 * Remet à 0 la valeur du select
 * @method reset
 * @instance
 * @memberOf wfw.dom.select
 */
/**
 * Supprime les éléments du select
 * @method remove
 * @instance
 * @memberOf wfw.dom.select
 * @param {...HTMLElement} [$e] Elements à supprimer
 */
/**
 * Supprime tous les groupes et toutes les options du select
 * @method removeAll
 * @instance
 * @memberOf wfw.dom.select
 */
/**
 * Renvoie un tableau dont les index correspondent aux valeurs associés aux éléments passés en
 * paramètres.
 * @method valueOf
 * @instance
 * @memberOf wfw.dom.select
 * @param {...HTMLElement} [$e] Element dont on souhaite obtenir les valeurs
 * @return Array Liste des valeurs aux index correspondants
 */
/**
 * Supprime les éléments du select
 * @method onChange
 * @instance
 * @memberOf wfw.dom.select
 * @param {SelectChangeCallback} Enregistre la fonction comme Callback appelé à chaque changement de
 *                               valeur du select
 */
/**
 * @callback SelectChangeCallback
 * @memberOf wfw.dom.select
 * @param {wfw.dom.select} select Instance du select concerné
 */
/**
 * Permet d'ajouter une option au select
 * @method addOpt
 * @instance
 * @memberOf wfw.dom.select
 * @param {SelectOption} $p Paramètres de l'option
 * @param {HTMLElement} [$to] Noeud de référence. Si groupe, ajoute au groupe. Sinon ajouté juste
 *                            avant l'option spécifiée.
 * @param {boolean|null|HTMLElement} [$before] Si null, et $to est un groupe, alors l'option est
 *                                             ajoutée à la fin du groupe.
 *                                             Si null, et $to est une option, alors l'option est
 *                                             ajoutée juste avant $to, dans le même groupe.
 *                                             Si true, l'option est ajoutée avant $to, dans le même
 *                                             groupe que son parent.
 *                                             Si false, l'option est ajoutée après $to, dans le même
 *                                             groupe que son parent.
 *                                             Si option ou groupe, alors l'option est ajoutée dans
 *                                             $to, avant l'option ou le groupe spécifié
 */
/**
 * Permet d'ajouter un groupe au select
 * @method addGroup
 * @instance
 * @memberOf wfw.dom.select
 * @param {SelectGroup} $p Paramètres du groupe
 * @param {HTMLElement} [$to] Noeud de référence. Si groupe, ajoute au groupe. Sinon ajouté juste
 *                            avant l'option spécifiée.
 * @param {boolean|null|HTMLElement} [$before] Si null, et $to est un groupe, alors le groupe est
 *                                             ajouté à la fin du groupe.
 *                                             Si null, et $to est une option, alors le groupe est
 *                                             ajoutée juste avant $to, dans le même groupe.
 *                                             Si true, le groupe est ajoutée avant $to, dans le même
 *                                             groupe que son parent.
 *                                             Si false, le groupe est ajoutée après $to, dans le même
 *                                             groupe que son parent.
 *                                             Si option ou groupe, alors le groupe est ajoutée dans
 *                                             $to, avant l'option ou le groupe spécifié
 */