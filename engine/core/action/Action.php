<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/02/18
 * Time: 08:13
 */

namespace wfw\engine\core\action;

use wfw\engine\core\request\IRequest;

/**
 * Action
 */
final class Action implements IAction
{
    /**
     * @var IRequest $_request
     */
    private $_request;
    /**
     * @var string $_internalPath
     */
    private $_internalPath;
    /**
     * @var null|string $_lang
     */
    private $_lang;
    /**
     * @var string[] $_availableLangs
     */
    private $_availableLangs;

    /**
     * Action constructor.
     *
     * @param IRequest    $request      Requête
     * @param string      $internalPath Chemin interne
     * @param null|string $lang         (optionnel) Langue dans l'url
     * @param array       $availableLangs (optionnel) Langues disponibles.
     */
    public function __construct(
        IRequest $request,
        string $internalPath,
        ?string $lang = null,
        array $availableLangs = [])
    {
        $this->_lang = $lang;
        $this->_request = $request;
        $this->_internalPath = $internalPath;
        $this->_availableLangs = $availableLangs;
    }

    /**
     * @return IRequest
     */
    public function getRequest(): IRequest
    {
        return $this->_request;
    }

    /**
     * @return null|string Langue
     */
    public function getLang(): ?string
    {
        return $this->_lang
            ?? array_keys($this->_request->getAcceptedLanguages($this->_availableLangs))[0]
            ?? null;
    }

    /**
     * @return string Chemin interne permettant de determiner le handler
     */
    public function getInternalPath(): string
    {
        return $this->_internalPath;
    }
}