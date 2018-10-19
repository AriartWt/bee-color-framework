<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 28/09/18
 * Time: 13:01
 */

namespace wfw\engine\package\contact\domain;

use wfw\engine\core\domain\aggregate\AggregateRoot;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\contact\domain\errors\ArchivingFailure;
use wfw\engine\package\contact\domain\errors\MarkAsReadFailed;
use wfw\engine\package\contact\domain\errors\MarkAsUnreadFailed;
use wfw\engine\package\contact\domain\events\ArchivedEvent;
use wfw\engine\package\contact\domain\events\ContactedEvent;
use wfw\engine\package\contact\domain\events\MarkedAsReadEvent;
use wfw\engine\package\contact\domain\events\MarkedAsUnreadEvent;
use wfw\engine\package\contact\domain\events\UnarchivedEvent;

/**
 * Prise de contact
 */
final class Contact extends AggregateRoot{
	/** @var IContactInfos $_infos */
	private $_infos;
	/** @var ContactLabel $_label */
	private $_label;
	/** @var boolean $_read */
	private $_read;
	/** @var boolean $_archived */
	private $_archived;

	/**
	 * Contact constructor.
	 *
	 * @param UUID          $id    Identifiant de l'aggrégat
	 * @param ContactLabel  $label Label de la prise de contact
	 * @param IContactInfos $infos Infos et contenu de la prise de contact
	 */
	public function __construct(UUID $id,ContactLabel $label,IContactInfos $infos) {
		parent::__construct(new ContactedEvent($id,$label,$infos));
	}

	/**
	 * @param string $user identifiant de l'utilisateur marquant la prise de contact comme lue
	 */
	public function markAsRead(string $user):void{
		if($this->_read) throw new MarkAsReadFailed("This contact is already marked as read");
		$this->registerEvent(new MarkedAsReadEvent($this->getId(),$user));
	}

	/**
	 * @param string $user utilisateur marquant la prise de contact comme non lue
	 */
	public function markAsUnread(string $user):void{
		if(!$this->_read) throw new MarkAsUnreadFailed("This contact havn't been read yet");
		$this->registerEvent(new MarkedAsUnreadEvent($this->getId(),$user));
	}

	/**
	 * @param string $user Utilisateur archivant la prise de contact
	 */
	public function archive(string $user):void{
		if($this->_archived) throw new ArchivingFailure("This contact have already been archived");
		$this->registerEvent(new ArchivedEvent($this->getId(),$user));
	}

	/**
	 * @param string $user Utilisateur désarchivant la prise de contact
	 */
	public function unarchive(string $user):void{
		if(!$this->_archived) throw new ArchivingFailure("This contact havn't been archived yet");
		$this->registerEvent(new UnarchivedEvent($this->getId(),$user));
	}

	/**
	 * @param ContactedEvent $e Evenement de prise de contact
	 */
	protected final function applyContactedEvent(ContactedEvent $e){
		$this->_label = $e->getLabel();
		$this->_infos = $e->getInfos();
		$this->_read = false;
		$this->_archived = false;
	}

	/**
	 * @param MarkedAsReadEvent $e La prise de contact a été marquée comme lue
	 */
	protected final function applyMarkedAsReadEvent(MarkedAsReadEvent $e):void{
		$this->_read = true;
	}

	/**
	 * @param MarkedAsUnreadEvent $e Evenement de marquage 'non lu'
	 */
	protected final function applyMarkedAsUnreadEvent(MarkedAsUnreadEvent $e):void{
		$this->_read = false;
	}

	/**
	 * @param UnarchivedEvent $e Evenement de désarchivage de la prise de contact
	 */
	protected final function applyUnarchivedEvent(UnarchivedEvent $e):void{
		$this->_archived = false;
	}

	/**
	 * @param ArchivedEvent $e La prise de contact a été archivée
	 */
	protected final function applyArchivedEvent(ArchivedEvent $e):void{
		$this->_archived = true;
	}
}