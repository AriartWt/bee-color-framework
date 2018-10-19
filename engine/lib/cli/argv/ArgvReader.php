<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 18/12/17
 * Time: 08:47
 */

namespace wfw\engine\lib\cli\argv;

/**
 * Class ArgvReader
 *
 * @package wfw\engine\lib\cli\argv
 */
class ArgvReader
{
    private $_result;
    private $_scriptName;
    private $_usage;
    private $_parser;

    /**
     *  ArgvReader constructor.
     *
     * @param IArgvParser $parser Parser d'arguments
     * @param array               $argv   Tableau d'arguments
     * @param string              $usage  Précision sur les manière d'utiliser la commande
     */
    public function __construct(IArgvParser $parser,array $argv, string $usage="Usage : ")
    {
        $this->_usage=$usage;
        $this->_parser = $parser;
        $this->_scriptName = array_shift($argv);
        try{
            $this->_result = $parser->parse($argv);
        }catch(\Exception $e){
            echo $e->getMessage();
            $this->echoUsage();
            exit(1);
        }
    }

    /**
     *  Retourne la valeur associée à l'argument $key
     *
     * @param string $key Nom de l'argument
     *
     * @return mixed
     */
    public function get(string $key){
        return $this->_result->get($key);
    }

    /**
     *  Teste l'existence d'une clé
     * @param string $key Clé à tester
     *
     * @return bool
     */
    public function exists(string $key):bool{
        return $this->_result->exists($key);
    }

    /**
     * @return string
     */
    public function getScriptName():string{
        return $this->_scriptName;
    }

    /**
     *  Envoie un helper pour l'utilisation de la commande vers la sortie standard.
     */
    protected function echoUsage():void{
        echo "\n$this->_scriptName : $this->_usage\n";
        foreach($this->_parser->getOptMap() as $opt){
            /** @var ArgvOpt $opt */
            echo "\t".$opt->getName()." : ".(($opt->isOptionnal())?'(Optionnal) ':'').$opt->getDescription()."\n";
        }
        echo "\n";
    }
}