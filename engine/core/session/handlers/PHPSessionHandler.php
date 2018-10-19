<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/02/18
 * Time: 11:12
 */

namespace wfw\engine\core\session\handlers;

/**
 * Le handler par défaut de PHP est utilisé par la session : cette classe est vide et n'eregistre
 * pas de nouveau session handler.
 */
final class PHPSessionHandler implements \SessionHandlerInterface
{
    public function close() {}
    public function destroy($session_id) {}
    public function gc($maxlifetime) {}
    public function open($save_path,$name) {}
    public function read($session_id) {}
    public function write($session_id, $session_data) {}
}