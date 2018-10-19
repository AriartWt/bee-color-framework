<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/10/17
 * Time: 03:15
 */

namespace wfw\engine\lib\debug;

/**
 *  Permet de debuger
 */
class Debuger
{
    private $_htmlPrintR;
    private $_print;

    /**
     *  Debuger constructor.
     *
     * @param bool    $print      Si false, la fonction debug ne fait rien
     * @param boolean $htmlPrintR (optionnel défaut : true) permet de rempalcer les sauts de ligne par des balises html br
     *
     */
    public function __construct(bool $print=true,bool $htmlPrintR=true)
    {
        $this->setHtmlPrintR($htmlPrintR);
        $this->_print = $print;
    }

    /**
     *  Change l'état de HtmlPrintR qui permet de rempalcer les sauts de ligne par des balises html br dans la fonciton debug
     * @param bool $state Nouvel état
     */
    public function setHtmlPrintR(bool $state):void{
        $this->_htmlPrintR = $state;
    }

    /**
     *  Change l'état d'affichage des debug. True : les affiche.
     * @param bool $state Nouvel état
     */
    public function setPrintState(bool $state):void{
        $this->_print=$state;
    }

    /**
     *  Fonction de debug. Permet l'affichage recursif sur la sortie standard du contenu d'une variable ainsi que la liste de slignes d'appel de la fonction.
     *
     * @param  mixed $var Variable à débuguer
     * @param bool   $force Force l'affichage même si _print est à false
     */
    function debug($var,bool $force=false):void{
        if(true || $force){
            $debug = debug_backtrace();
            echo '<p><a href="#"><strong>'.$debug[0]['file'].' </strong> ligne '.$debug[0]['line'].'</a></p>';//onclick="$(this).parent().next(\'ol\').slideToggle(); return false;"
            echo '<ol>';//style="display:none;"
            foreach ($debug as $k => $v){
                if($k > 0 && isset($v["file"]) && isset($v['line'])){
                    echo '<li><strong>'.$v['file'].' </strong> ligne '.$v['line'].'</li>';
                }
            }
            echo '</ol>';
            echo '<pre>';
            if($this->_htmlPrintR){
                echo str_replace("\n","</br>",print_r($var,true));
            }else{
                print_r($var);
            }
            echo '</pre>';
        }
    }

    /**
     *  Retourne un debugger
     *
     * @param bool $print      voir constructeur
     * @param bool $htmlPrintR voir constructeur
     *
     * @return Debuger
     */
    public static function get(bool $print=true,bool $htmlPrintR=true){
        return new self($print,$htmlPrintR);
    }
}