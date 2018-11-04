<?php
namespace wfw\daemons\kvstore\server\requests;

/**
 *  Demande la destruction d'une session.
 */
final class LogoutRequest extends AbstractKVSRequest {
	/**
	 * DeconnectionRequest constructor.
	 *
	 * @param string $sessionId Identifiant de session à déconnecter
	 */
	public function __construct(string $sessionId) {
		parent::__construct($sessionId);
	}
}