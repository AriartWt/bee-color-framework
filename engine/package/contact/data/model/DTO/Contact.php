<?php
namespace wfw\engine\package\contact\data\model\DTO;

use wfw\engine\core\data\model\DTO\DTO;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\contact\domain\ContactLabel;
use wfw\engine\package\contact\domain\IContactInfos;

/**
 * DTO Prise de contact
 */
class Contact extends DTO {
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
	 * @param bool          $archived
	 * @param float|null    $archivingDate
	 */
	public function __construct(
		UUID $id,
		ContactLabel $label,
		IContactInfos $infos,
		float $creationDate,
		bool $readed=false,
		?float $readDate=null,
		bool $archived=false,
		?float $archivingDate=null
	){
		parent::__construct($id);
		$this->_label = $label;
		$this->_readDate = $readDate;
		$this->_infos = $infos;
		$this->_readed = $readed;
		$this->_creationDate = $creationDate;
		$this->_archived = $archived;
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
	 * @return array
	 */
	public function transformProperties(): array {
		return array_merge(parent::transformProperties(),[
			"_infos" => (string) $this->_infos,
			"_label" => (string) $this->_label
		]);
	}
}