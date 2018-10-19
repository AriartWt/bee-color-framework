<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/02/18
 * Time: 09:55
 */

namespace wfw\engine\package\users\domain\repository;

use wfw\engine\core\command\ICommand;
use wfw\engine\package\users\domain\User;

/**
 * Interface d'un repository pour les utilisateurs.
 */
interface IUserRepository
{
	/**
	 * @param string $id Identifiant de l'utilisateur
	 * @return null|User
	 */
	public function get(string $id):?User;
	
	/**
	 * @param string[] $ids Identifiants des utilisateurs
	 * @return array
	 */
	public function getAll(string... $ids):array;
	
	/**
	 * Ajoute ou modifie un utilisateur
	 *
	 * @param User          $user Utilisateur
	 * @param ICommand|null $cmd  Commande
	 */
	public function add(User $user,?ICommand $cmd = null):void;
	
	/**
	 * Ajoute de sutilisateurs au repository
	 *
	 * @param null|ICommand $cmd Commande à l'orgine de l'ajout
	 * @param User          ...$users Utilisateurs à ajouter
	 */
	public function addAll(?ICommand $cmd = null,User... $users):void;
	
	/**
	 * Modifie l'utilisateur dans le repository
	 *
	 * @param User          $user utilisateur modifié
	 * @param null|ICommand $cmd Commande à l'origine de la modification
	 */
	public function modify(User $user,?ICommand $cmd = null):void;
	
	/**
	 * Modifie des utilisateurs dans le repository
	 *
	 * @param null|ICommand $cmd Commandes à l'origine des modifications
	 * @param User          ...$users utilisateurs à modifier
	 */
	public function modifyAll(?ICommand $cmd = null,User... $users):void;
	
	/**
	 * @param User          $user Utilisateur à supprimer
	 * @param null|ICommand $cmd Commade à l'origine de la suppression
	 */
	public function remove(User $user,?ICommand $cmd = null):void;
	
	/**
	 * @param null|ICommand $cmd Commande à l'origine des suppressions
	 * @param User          ...$users utilisateurs à supprimer
	 */
	public function removeAll(?ICommand $cmd = null,User... $users):void;
}