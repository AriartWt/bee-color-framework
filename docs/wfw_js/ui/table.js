/**
 * @typedef {Object} SortDict
 * @property {"asc"|"desc"|null} [default] Tri par defaut (defaut : null)
 * @property {"asc"|"desc"|null} [first] Premier tri lors du clic (defaut : asc)
 * @property {boolean} [disabled] Tri désactivé pour la colonne.
 */
/**
 * @typedef {Object} TableColumnDict
 * @property {string} name Nom de la colonne
 * @property {function} [displayer] Fonction pour le formattage des données (data) => string
 * @property {function} [comparator] Fonction pour le tri (v1,v2)=>integer
 * @property {Object} [cellEvents] Liste des événements à attacher à chaque cellule.
 * @property {SortDict} [sort] Paramètres du tri
 */
/**
 * @function table
 * @memberOf wfw.ui
 * @param {TableColumnDict[]} $columns Liste des colonnes
 * @param {Object} [$params] Liste des paramètres de chaque ligne {on:{click:()=>...},className:".."}
 * @property {HTMLElement} body tbody du tableau
 * @property {HTMLElement} head thead du table
 * @property {HTMLElement} html tableau html
  */
/**
 * Retourne la ligne $i
 * @function table#get
 * @memberOf wfw.ui.table
 * @param {int} $i indexe
 */
/**
 * Retourne les données associées à la ligne tr
 * @function table#getRowData
 * @memberOf wfw.ui.table
 * @param {HTMLElement} $row Ligne du tableau
 */
/**
 * Retourne la position de la ligne dans le tableau
 * @function table#getRowIndex
 * @memberOf wfw.ui.table
 * @param {HTMLElement} $row Ligne du tableau
 * @return {int}
 */
/**
 * Ajoute une ligne au tableau. Tiens compte du tri.
 * @function table#addRow
 * @memberOf wfw.ui.table
 * @param {...*} Données de la ligne
 */
/**
 * Modifie une ligne du tableau. Tiens compte du tri.
 * @function table#editRow
 * @memberOf wfw.ui.table
 * @param {HTMLElement} $row Ligne à editer
 * @param {object} $data Données sous forme {index/name : data}
 */
/**
 * Supprime la ligne spécifiée
 * @function table#removeRow
 * @memberOf wfw.ui.table
 * @param {HTMLElement} $row Ligne à supprimer
 */
/**
 * Supprime la ligne située à la position spécifiée
 * @function table#removeRowAt
 * @memberOf wfw.ui.table
 * @param {int} $index Indexe de la ligne à supprimer
 */
/**
 * Supprime $len lignes depuis la position $start. Si la position n'est pas spécifiée, $len deviens
 * la taille du tableau
 * @function table#removeRows
 * @memberOf wfw.ui.table
 * @param {int} $start Indexe du premier élément à supprimer.
 * @param {int} [$len] Nombre d'éléments à supprimer
 */