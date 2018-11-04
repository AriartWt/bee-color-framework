<?php
namespace wfw\daemons\kvstore\server\containers\request\write;

use wfw\daemons\kvstore\server\containers\data\storages\StorageKey;
use wfw\daemons\kvstore\server\requests\AbstractKVSRequest;

/**
 *  Base d'implémentation pour une requête d'écriture
 */
abstract class AbstractWriteRequest extends AbstractKVSRequest implements IKVSWriteContainerRequest {
	/** @var string $_key */
	protected $_key;

	/**
	 * AbstractWriteRequest constructor.
	 *
	 * @param string     $sessId Identifiant de session
	 * @param StorageKey $key    Clé concernée
	 */
	public function __construct(string $sessId,StorageKey $key) {
		parent::__construct($sessId);
		$this->_key = (string) $key;
	}

	/**
	 * @return string
	 */
	public function getKey():string{
		return $this->_key;
	}
}