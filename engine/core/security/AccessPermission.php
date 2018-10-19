<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/02/18
 * Time: 13:47
 */

namespace wfw\engine\core\security;

use wfw\engine\core\response\IResponse;

/**
 * Permission
 */
final class AccessPermission implements IAccessPermission
{
    /**
     * @var null|string $_code
     */
    private $_code;
    /**
     * @var bool $_granted
     */
    private $_granted;
    /**
     * @var null|string $_message
     */
    private $_message;
    private $_response;

    /**
     * AccessPermission constructor.
     *
     * @param bool           $granted True: accordé, false sinon
     * @param null|string    $code    (optionnel) code
     * @param null|string    $message (optionnel) message
     * @param IResponse|null $response (optionnel) Réponse à afficher à l'utilisateur
     */
    public function __construct(
        bool $granted,
        ?string $code = null,
        ?string $message = null,
        IResponse $response = null)
    {
        $this->_code = $code;
        $this->_message = $message;
        $this->_granted = $granted;
        $this->_response = $response;
    }

    /**
     * @return bool Permission accordée ou non.
     */
    public function isGranted(): bool
    {
        return $this->_granted;
    }

    /**
     * @return null|string Code
     */
    public function getCode(): ?string
    {
        return $this->_code;
    }

    /**
     * @return null|string Message
     */
    public function getMessage(): ?string
    {
        return $this->_message;
    }

    /**
     * @return null|IResponse Réponse
     */
    public function getResponse(): ?IResponse
    {
        return $this->_response;
    }
}