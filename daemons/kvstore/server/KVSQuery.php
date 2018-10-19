<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/01/18
 * Time: 08:47
 */

namespace wfw\daemons\kvstore\server;

use wfw\daemons\kvstore\socket\io\KVSSocketIO;

/**
 *  Requête crée par le KVSServer et mise en attente de réponse de la part de l'un dex workers.
 */
final class KVSQuery implements IKVSQuery
{
    /**
     * @var KVSSocketIO $_io
     */
    private $_io;
    /**
     * @var IKVSInternalRequest $_request
     */
    private $_request;

    /**
     * @var int $_expirationDate
     */
    private $_expirationDate;

    /**
     * @var int $_generationDate
     */
    private $_generationDate;

    /**
     * KVSQuery constructor.
     *
     * @param KVSSocketIO                 $io      Interface de réponse
     * @param IKVSInternalRequest $request Requête
     * @param int                         $expirationDate Date d'expiration.
     */
    public function __construct(KVSSocketIO $io,IKVSInternalRequest $request,int $expirationDate)
    {
        $this->_io = $io;
        $this->_request = $request;
        $this->_generationDate = microtime(true);
        if($expirationDate<$this->_generationDate){
            throw new \InvalidArgumentException("Cannot create an outdated query !");
        }else{
            $this->_expirationDate = $expirationDate;
        }
    }

    /**
     * @return KVSSocketIO Client ayant envoyé la requête
     */
    public function getIO(): KVSSocketIO
    {
        return $this->_io;
    }

    /**
     * @return IKVSInternalRequest Requête interne envoyée à l'un des worker.
     */
    public function getInternalRequest(): IKVSInternalRequest
    {
        return $this->_request;
    }

    /**
     * @return int Date d'expiration de la requête.
     */
    public function getExpirationDate(): int
    {
        return $this->_expirationDate;
    }

    /**
     * @return int Date à laquelle la requête a été créée
     */
    public function getGenerationDate() : int{
        return $this->_generationDate;
    }
}