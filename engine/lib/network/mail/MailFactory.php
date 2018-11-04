<?php
namespace wfw\engine\lib\network\mail;

use Dice\Dice;

/**
 * Permet de créer des mails concernant les utilisateurs en se basant sur Dice
 */
final class MailFactory implements IMailFactory{
	/** @var Dice $_dice */
	private $_dice;

	/**
	 * UserMailFactory constructor.
	 * @param Dice $dice Instance de dice pour les création des mails
	 */
	public function __construct(Dice $dice){
		$this->_dice = $dice;
	}

	/**
	 * @param string $type Type de mail à créer
	 * @param array $args Arguments à passer au constructeur du mail
	 * @return IMail
	 */
	public function create(string $type, array $args = []): IMail{
		if(!is_a($type,IMail::class,true))
			throw new \InvalidArgumentException("$type is not an instance of ".IMail::class);
		/** @var IMail $res */
		$res = $this->_dice->create($type,$args);
		return $res;
	}
}