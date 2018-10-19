<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/04/18
 * Time: 11:39
 */

namespace wfw\engine\package\news\command\errors;

use wfw\engine\core\command\errors\CommandFailure;

/**
 * L'article n'a pas été trouvé
 */
final class ArticleNotFound extends CommandFailure{
	/**
	 * ArticleNotFound constructor.
	 *
	 * @param string $id identifiant de l'article concerné
	 */
	public function __construct(string $id) {
		parent::__construct("Article $id doesn't exists !");
	}
}