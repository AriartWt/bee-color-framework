<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 14/02/18
 * Time: 11:11
 */

namespace wfw\engine\core\session;

use wfw\engine\core\session\handlers\PHPSessionHandler;

/**
 * Session
 */
final class Session implements ISession
{
    /**
     * @var string $_tmp
     */
    private $_tmp;

    /**
     * @var string $_logKey
     */
    private $_logKey;

    /**
     *  Démarre la Session
     *
     * @param null|string $logKey (optionnel defaut : user) Clé d'accés au champ contenant les
     *                            informations de session d'un utilisateur connecté.
     * @param null|string $tmp    (optionnel ) Chemin par défaut vers un dossier
     *                            temporaire
     * @param \SessionHandlerInterface $handler (optionnel) Handler de session
     */
    public function __construct(
        string $logKey = "user",
        ?string $tmp=null,
        \SessionHandlerInterface $handler=null
    ){
        if(!isset($_SESSION)){
            if(!is_null($handler) && !( $handler instanceof PHPSessionHandler)){
                ini_set('session.save_handler','user');
                ini_set('session.use_strict_mode',true);
                session_set_save_handler($handler,true);
            }
        }
        $this->_tmp = $tmp ?? ENGINE.DS."resources".DS."tmp";
        $this->_logKey = $logKey;
        session_start();
    }

    /**
     *   Détruit la session
     */
    public function destroy(){
        session_destroy();
        session_start();
    }

    /**
     * Crée un dossier temporaire et en retourne le chemin.
     *
     * @return string Chemin d'accés au dossier temporaire
     */
    public function getTmp():string{
        $time=microtime();
        $time=str_replace("0.","",$time);
        $time=explode(' ',$time);
        $time=$time[1]."_".$time[0];
        $tmp=$this->_tmp.DS.session_id().DS.$time;
        if(!file_exists($tmp)){
            mkdir($tmp,true);
        }
        return $tmp;
    }

    /**
     *	 Permet d'écrire dans la session
     *	@param string $key est la clé à inscrire dans la session
     *	@param mixed $value est la valeur correspondante
     **/
    public function set($key,$value):void{
        $_SESSION[$key]=$value;
    }

    /**
     *	 Permet de lire une clé
     *
     *	@param string $key nom de la clé à lire
     * @return mixed|null retourne la session si aucune clé passée, retourne la valeur de la clé si
     *                    elle existe, retourne null sinon
     **/
    public function get($key=null){
        if($key){
            if(isset($_SESSION[$key])){
                return $_SESSION[$key];
            }else{
                return null;
            }
        }else{
            return $_SESSION;
        }
    }

    /**
     *  Permet de supprimer une clé dans la session
     * @param string $key clé à supprimer
     */
    public function remove($key):void{
        if(isset($_SESSION[$key])){
            unset($_SESSION[$key]);
        }
    }

    /**
     *  Remplace la valeur d'une clé par une nouvelle valeur
     * @param  string $key      Clé à rempalcer
     * @param  mixed  $newValue Nouvelle valeur à insérer
     */
    public function replace($key,$newValue){
        if($this->get($key)){
            $this->remove($key);
            $this->set($key,$newValue);
        }
    }

    /**
     *  Permet de savoir si une clé est présente dans la session
     *
     * @param $key Clé à tester
     *
     * @return bool
     */
    public function exists($key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * @return bool Permet de savoir si un utilisateur loggé est enregistré.
     */
    public function isLogged(): bool
    {
        return isset($_SESSION[$this->_logKey]);
    }
}