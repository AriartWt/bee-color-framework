<?php
namespace wfw\engine\core\notifier;
use wfw\engine\core\notifier\printer\IPrinter;
use wfw\engine\core\session\ISession;

/**
 * Ne garde en mémoire qu'un seul message. A chaque ajout, le précédent est détruit. Se sert de la
 * session pour stocker ses messages.
 */
final class FlashNotifier implements INotifier {
	/** @var IPrinter $_printer */
	private $_printer;
	/** @var ISession $_session */
	private $_session;
	/** @var string $_sessionKey */
	private $_sessionKey;

	/**
	 * FlashNotifier constructor.
	 *
	 * @param IPrinter $printer Printer pour l'affichage du message
	 * @param ISession $session Session pour le stockage du message
	 */
	public function __construct(IPrinter $printer,ISession $session) {
		$this->_printer = $printer;
		$this->_session = $session;
		$this->_sessionKey = self::class."::flash_notifier";
	}

	/**
	 * Consomme un message. Premier arrivé, premier servi.
	 *
	 * @return null|string Représentation du message. Null s'il n'y en a pas.
	 */
	public function print(): ?string {
		$message = $this->_session->get($this->_sessionKey);
		if(!is_null($message)){
			$this->_session->remove($this->_sessionKey);
			return $this->_printer->print($message);
		}else{
			return null;
		}
	}

	/**
	 * Consomme tous les messages.
	 *
	 * @return null|string Représentation des messages (ordre premier arrivé, premier affiché). Null
	 *                     s'il n'y en a pas.
	 */
	public function printAll(): ?string {
		return $this->print();
	}

	/**
	 * Remet à zéro la liste des messages du notifier et renvoie l'ancien tableau de messages
	 *
	 * @return array
	 */
	public function reset(): array {
		$message = $this->_session->get($this->_sessionKey);
		$this->_session->remove($this->_sessionKey);
		if(!is_null($message)){
			return [$message];
		}else{
			return [];
		}
	}

	/**
	 * Ajoute un message
	 *
	 * @param IMessage $message Message à ajouter
	 */
	public function addMessage(IMessage $message): void {
		$this->_session->set($this->_sessionKey,$message);
	}
}