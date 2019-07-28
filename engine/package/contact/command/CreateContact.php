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

	/**
	 * CreateContact constructor.
	 *
	 * @param ContactLabel  $label
	 * @param IContactInfos $infos
	 * @param string        $userId Identifiant de l'utilisateur à l'origi
	 */
	public function __construct(ContactLabel $label, IContactInfos $infos, ?string $userId=null) {
		parent::__construct($userId);
		$this->_label = $label;
		$this->_infos = $infos;
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
}