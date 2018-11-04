<?php
namespace wfw\engine\package\contact\command;

use wfw\engine\package\contact\domain\ContactLabel;
use wfw\engine\package\contact\domain\IContactInfos;

/**
 * Crée une prise de contact
 */
final class CreateContact extends ContactCommand {
	/** @var ContactLabel $_label */
	private $_label;
	/** @var IContactInfos $_infos */
	private $_infos;
	/** @var string $_userId */
	private $_userId;

	/**
	 * CreateContact constructor.
	 *
	 * @param ContactLabel  $label
	 * @param IContactInfos $infos
	 * @param string        $userId Identifiant de l'utilisateur à l'o
	 */
	public function __construct(ContactLabel $label, IContactInfos $infos, ?string $userId=null) {
		parent::__construct();
		$this->_label = $label;
		$this->_infos = $infos;
		$this->_userId = $userId;
	}

	/**
	 * @return ContactLabel
	 */
	public function getLabel(): ContactLabel {
		return $this->_label;
	}

	/**
	 * @return IContactInfos
	 */
	public function getInfos(): IContactInfos {
		return $this->_infos;
	}

	/**
	 * @return null|string
	 */
	public function getUserId(): ?string {
		return $this->_userId;
	}
}