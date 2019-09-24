/**
 * @typedef Object WheelMenuItem
 * @param [string|HTMLElement] icon Icone à afficher
 * @param [Object[]] btns Liste des boutons à afficher au centre sous la forme d'un objet de création
 *                         passé à la fonction wfw.dom.create() pour chaque bouton.
 * @param [Object] [params] Paramètres passés lors de la création de la catégorie à la fonction
 *                          wfw.dom.create()
 */
/**
 * @typedef Object WheelMenuDict
 * @param {int} [radius] Diamètre d'écartement des catégories en px. Defaut : 160
 * @param {string} [css] Lien vers le fichier CSS à charger pour les styles du menu. (en replacement
 *                       de celui par défaut sous engine/webroot/Css/api/ui/wheelMenu.css
 * @param {WheelMenuItem[]} items Liste des catégories à créer dans le menu.
 */
/**
 * Crée un menu de profondeur 2 sous forme de roue dont les catégories principales sont disposées
 * de façon homogène autour du centre.
 * Les différents choix de chacune de ces catégories sont visibles au centre au survol de la
 * catégorie par la souris.
 * Le centre est animé de sorte à pointer la catégorie affichée.
 * Si un seul choix est disponible pour une catégorie, un clic sur la catégorie déclenche un clique
 * l'unique choix.
 *  @function wheelMenu
 *  @constructor wheelMenu
 *  @memberOf wfw.ui
 *  @param {WheelMenuDict} $params Paramètres de création du menu
 *  @property {HTMLElement} html Représentation HTML du menu (à insérer dans la page)
 */
/**
 * Mets à jour la position des items de la roue. Particulèrement utile si la taille de certains
 * de ses objet est modifiée ou si la roue est ajoutée dans un contener non visible (puisque
 * les calculs de position se basent sur offsetHeight et offsetWidth qui valent 0 dans un
 * container non rendu à l'écran par le navigateur.
 * @function wheelMenu#update
 * @memberOf wfw.ui.wheelMenu
 */