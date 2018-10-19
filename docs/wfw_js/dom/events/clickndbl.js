
/**
 * Permet d'ajouter à la fois l'événement click et dblclick à un élément.
 * @function clickndbl
 * @memberOf wfw.dom.events
 * @param {HTMLElement} $elem Noeud auquel attacher les événements click et dblclick
 * @param {ClickNDBLEventHandler} $click Callback appelé lorsqu'un click est déclenché.
 * @param {ClickNDBLEventHandler} $dbl Callback appelé lorsqu'un dblclick est déclenché.
 * @return {HTMLElement} Noeud auquel ont été attachés les événements
 */
/**
 * @callback ClickNDBLEventHandler
 * @param Event $e Evenement reçu
 * @param HTMLElement $el Element concerné
 */