<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/01/18
 * Time: 11:19
 */

namespace wfw\daemons\modelSupervisor\socket\data;

/**
 * Résultat du parsing d'une réponse ou requete du KVS.
 * _data et _params sont complémentaire. Lorsque _params est précisé, il contient la requête/réponse sérialisée, et _data
 * des données volumineuses que l'on ne manipule pas pour des raison de performances.
 * Si _params est une chaine vide, alors c'est _data qui contient la requête/réponse serialisée. Ou aucune donnée.
 */
final class MSServerDataParserResult
{
    private $_class;
    private $_data;
    private $_sessId;
    private $_queryId;
    private $_userName;
    private $_serverKey;
    private $_params;

    /**
     * MSServerDataParserResult constructor.
     *
     * @param string $class     Contient le nom de la classe de la requête à traiter.
     * @param string $data      Données de la requête. Objet à déserialiser.
     * @param string $sessId    Identifiant de dessions
     * @param string $queryId   Identifiant de requête
     * @param string $serverKey Clé du serveur
     * @param string $userName  Nom de l'utilisateur
     * @param string $params    Paramètres associés à la requête. Objet à déserialiser.
     */
    public function __construct(
        string $class,
        string $data,
        string $sessId,
        string $queryId,
        string $serverKey,
        string $userName,
        string $params)
    {
        $this->_class = $class;
        $this->_data = $data;
        $this->_userName = $userName;
        $this->_sessId = $sessId;
        $this->_queryId = $queryId;
        $this->_serverKey = $serverKey;
        $this->_params = $params;
    }

    /**
     * @param string $str Classe à tester
     * @return bool True si la classe de la requête courante est un instance de $str.
     */
    public function instanceOf(string $str):bool{
        return is_a($this->_class,$str,true);
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->_class;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->_data;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->_sessId;
    }

    /**
     * @return string
     */
    public function getQueryId(): string
    {
        return $this->_queryId;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->_userName;
    }

    /**
     * @return string
     */
    public function getServerKey(): string
    {
        return $this->_serverKey;
    }

    /**
     * @return string
     */
    public function getParams():string{
        return $this->_params;
    }

    /**
     * @return string Retourne les données à désérialiser.
     */
    public function getDataToUnserialize():string{
        if(empty($this->_params)){
            return $this->_data;
        }else{
            return $this->_params;
        }
    }
}