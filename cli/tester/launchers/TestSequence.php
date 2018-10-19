<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 29/05/18
 * Time: 16:17
 */

namespace wfw\cli\tester\launchers;

use wfw\cli\tester\contexts\ITestsEnvironment;

/**
 * Séquence de tests
 */
abstract class TestSequence implements ITestSequence
{
    /** @var string[] $_paths */
    private $_paths;
    /** @var string[] $_environment */
    private $_environment;
    /** @var string $_description */
    private $_description;

    /**
     * TestSequence constructor.
     *
     * @param array  $paths Liste des dossiers/fichiers à executer lors de la séquence de test
     * @param array  $environment Environnements de tests à lancer
     * @param string $description Description de la séquence
     */
    public function __construct(array $paths, array $environment = [], string $description='') {
        $this->_paths = (function(string ...$paths){
            foreach($paths as $p){
                if(!file_exists($p)) throw new \InvalidArgumentException(
                    "$p is not a valide path !"
                );
            }
            return $paths;
        })(...$paths);
        $this->_environment = (function(string ...$environments){
            foreach($environments as $env){
                if(!is_a($env,ITestsEnvironment::class,true))
                    throw new \InvalidArgumentException("$env doesn't implements ".ITestsEnvironment::class);
            }
            return $environments;
        })(...$environment);
        $this->_description = $description;
    }

    /**
     * @return string[]
     */
    protected function getPaths(): array { return $this->_paths; }

    /**
     * @return string[]
     */
    protected function getEnvironments(): array { return $this->_environment; }

    /**
     * @return string
     */
    protected function getDescription(): string { return $this->_description; }
}