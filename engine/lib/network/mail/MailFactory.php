<?php
namespace wfw\engine\lib\network\mail;

use wfw\engine\core\app\factory\IGenericAppFactory;

/**
 * Permet de créer des mails concernant les utilisateurs en se basant sur Dice
 */
final class MailFactory implements IMailFactory{
	/** @var IGenericAppFactory $_factory */
	private $_factory;

	/**
	 * UserMailFactory constructor.
	 * @param IGenericAppFactory $factory Instance de dice pour les création des mails
	 */
	public function __construct(IGenericAppFactory $factory){
		$this->_factory = $factory;
	}

	/**
	 * @param string $type Type de mail à créer
	 * @param array $args Arguments à passer au constructeur du mail
	 * @return IMail
	 */
	public function create(string $type, array $args = []): IMail{
		return $this->_factory->create($type,$args,[IMail::class]);
	}
}