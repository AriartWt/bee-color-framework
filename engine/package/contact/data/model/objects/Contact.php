<?php
namespace wfw\engine\package\contact\data\model\objects;

use wfw\engine\core\data\model\DTO\IDTO;
use wfw\engine\core\data\model\IModelObject;
use wfw\engine\core\data\model\ModelObject;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\contact\domain\ContactLabel;
use wfw\engine\package\contact\domain\IContactInfos;

/**
 * Class Contact
 *
 * @package wfw\engine\package\contact\data\model\objects
 */
class Contact extends ModelObject{
	/** @var ContactLabel $_label */
	private $_label;
	/** @var bool $_readed */
	private $_readed;
	/** @var float|null $_readDate */
	private $_readDate;
	/** @var IContactInfos $_infos */
	private $_infos;
	/** @var float $_creationDate */
	private $_creationDate;
	/** @var bool $_archived */
	private $_archived;
	/** @var float|null $_archivingDate */
	private $_archivingDate;

	/**
	 * Contact constructor.
	 *
	 * @param UUID          $id           Identifiant
	 * @param ContactLabel  $label        Initulé de la prise de contact
	 * @param IContactInfos $infos        Informations de la prise de contact
	 * @param float         $creationDate Date de création
	 * @param bool          $readed       La prise de contact a été lue
	 * @param float|null    $readDate     Date à laquelle la prise de contact a été marquée comme lue
	 * @param bool          $archived     La prise de contact est archivée
	 * @param float|null    $archivingDate Date de l'archivage
	 */
	public function __construct(
		UUID $id,
		ContactLabel $label,
		IContactInfos $infos,
		float $creationDate,
		bool $readed=false,
		?float $readDate=null,
		bool $archived = false,
		?float $archivingDate = null
	){
		parent::__construct($id);
		$this->_label = $label;
		$this->_readDate = $readDate;
		$this->_infos = $infos;
		$this->_readed = $readed;
		if($readed && is_null($readDate)) throw new \InvalidArgumentException(
			"Read date must be given if current contact state is Read"
		);
		$this->_creationDate = $creationDate;
		$this->_archived = $archived;
		if($archived && is_null($archivingDate)) throw new \InvalidArgumentException(
			"Archiving date must be given if current contact state is Archived"
		);
		$this->_archivingDate = $archivingDate;
	}

	/**
	 * @return ContactLabel
	 */
	public function getLabel(): ContactLabel {
		return $this->_label;
	}

	/**
	 * @return bool
	 */
	public function isRead(): bool {
		return $this->_readed;
	}

	/**
	 * @return bool
	 */
	public function isArchived(): bool {
		return $this->_archived;
	}

	/**
	 * @return float|null
	 */
	public function getArchivingDate(): ?float {
		return $this->_archivingDate;
	}

	/**
	 * @return float|null
	 */
	public function getReadDate(): ?float {
		return $this->_readDate;
	}

	/**
	 * @return IContactInfos
	 */
	public function getInfos(): IContactInfos {
		return $this->_infos;
	}

	/**
	 * @return float
	 */
	public function getCreationDate(): float {
		return $this->_creationDate;
	}

	/**
	 * Marque la prise de ocntact courante comme lue
	 * @param float $readDate Date à laquelle la prise de contact a été marquée comme lue
	 */
	public function markAsRead(float $readDate):void{
		$this->_readed = true;
		$this->_readDate = $readDate;
	}

	/**
	 * Marque la prise de contact courante comme non lue
	 */
	public function markAsUnread():void{
		$this->_readDate = null;
		$this->_readed = false;
	}

	/**
	 * Archive la prise de contact courante
	 * @param float $archivingDate Date d'archivage
	 */
	public function archive(float $archivingDate):void{
		$this->_archived = true;
		$this->_archivingDate = $archivingDate;
	}

	/**
	 * Désarchive la prise de contact courante
	 */
	public function unarchive():void{
		$this->_archived = false;
		$this->_archivingDate = null;
	}

	/**
	 * @param IModelObject $o
	 * @return int
	 */
	public function compareTo(IModelObject $o): int {
		/** @var Contact $o */
		if($this->_creationDate === $o->getCreationDate()) return 0;
		else return $this->getCreationDate() - $o->getCreationDate() < 0 ? -1 : 1;
	}

	/**
	 *  Transforme l'objet courant en DTO pour garder la cohérence du Model
	 *
	 * @return IDTO
	 */
	public function toDTO(): IDTO {
		return new \wfw\engine\package\contact\data\model\DTO\Contact(
			$this->getId(),
			$this->_label,
			$this->_infos,
			$this->_creationDate,
			$this->_readed,
			$this->_readDate,
			$this->_archived,
			$this->_archivingDate
		);
	}
}