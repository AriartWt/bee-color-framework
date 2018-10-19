<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/04/18
 * Time: 09:09
 */

namespace wfw\engine\package\news\domain\events;

use wfw\engine\core\domain\events\DomainEvent;

/**
 * Evenement concernant un article
 */
abstract class ArticleEvent extends DomainEvent {}