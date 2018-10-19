<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/09/18
 * Time: 17:15
 */

namespace wfw\engine\package\contact\domain\events;

use wfw\engine\core\domain\events\IAggregateRootGeneratedEvent;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\contact\domain\ContactLabel;
use wfw\engine\package\contact\domain\IContactInfos;

/**
 * L'application a reçu une demande de contact
 */
final class ContactedEvent extends ContactEvent implements IAggregateRootGeneratedEvent{
	/** @var IContactInfos $_infos */
	private $_infos;
	/** @var ContactLabel $_label */
	private $_label;
	/** @var array $_args */
	private $_args;

	/**
	 * ContactedEvent constructor.
	 *
	 * @param UUID          $aggregateId
	 * @param ContactLabel  $label Label du formulaire de ocntact
	 * @param IContactInfos $infos Informations remplies par l'utilisateur
	 */
	public function __construct(UUID $aggregateId,ContactLabel $label, IContactInfos $infos) {
		parent::__construct($aggregateId);
		$this->_infos = $infos;
		$this->_label = $label;
		$this->_args = func_get_args();
	}

	/**
	 * @return IContactInfos
	 */
	public function getInfos(): IContactInfos {
		return $this->_infos;
	}

	/**
	 * @return ContactLabel
	 */
	public function getLabel(): ContactLabel {
		return $this->_label;
	}

	/**
	 * @return array Arguments du constructeur de l'aggrégat
	 */
	public function getConstructorArgs(): array {
		return $this->_args;
	}
}