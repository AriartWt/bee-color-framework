<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/04/18
 * Time: 11:02
 */

namespace wfw\engine\package\users\data\model\specs;

use wfw\engine\core\data\specification\LeafSpecification;
use wfw\engine\package\users\data\model\objects\User;

/**
 * Recherche un utilisateur dont le login correspond à un login particulier
 */
final class LoginIs extends LeafSpecification
{
	/**  @var string $_login */
	private $_login;

	/**
	 * LoginIs constructor.
	 *
	 * @param string $login Login à trouver
	 */
	public function __construct(string $login)
	{
		parent::__construct();
		$this->_login = $login;
	}

	/**
	 *  Verifie que le candidat correspond à la spécification
	 *
	 * @param mixed $candidate Candidat à la specification
	 *
	 * @return bool
	 */
	public function isSatisfiedBy($candidate): bool
	{
		/** @var User $candidate */
		return (string)$candidate->getLogin() === $this->_login;
	}
}