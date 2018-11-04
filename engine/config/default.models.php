<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 02/01/18
 * Time: 12:46
 */

use wfw\engine\package\users\data\model\UserModel;

return [
    UserModel::class,
	\wfw\engine\package\contact\data\model\ContactModel::class,
	\wfw\engine\package\news\data\model\ArticleModel::class
];