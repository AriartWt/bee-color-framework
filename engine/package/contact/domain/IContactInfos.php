<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 28/09/18
 * Time: 13:02
 */

namespace wfw\engine\package\contact\domain;

/**
 * Une demande de contact aura toujours :
 * -infos diverses
 * -un contenu
 * De toute façon, les infos sont printables.
 */
interface IContactInfos {
	public function __toString();
}