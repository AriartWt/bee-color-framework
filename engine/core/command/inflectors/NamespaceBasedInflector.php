<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 11/02/18
 * Time: 08:36
 */

namespace wfw\engine\core\command\inflectors;

use wfw\engine\core\command\errors\NoCommandHandlerFound;
use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandHandler;
use wfw\engine\core\command\ICommandHandlerFactory;
use wfw\engine\core\command\ICommandInflector;

/**
 * Tente de résoudre les CommandHandlers en se basant sur le namespace de la commande.
 * Exemple : la commande \wfw\engine\packages\users\commands\CreateUser
 *           sera résolue : \wfw\engine\packages\users\commands\handlers\CreateUserHandler
 */
final class NamespaceBasedInflector implements ICommandInflector
{
    /** @var ICommandHandlerFactory $_factory */
    private $_factory;
    /** @var ICommandHandler[][] $_handlers */
    private $_handlers;

    /**
     * NamespaceBasedInflector constructor.
     *
     * @param ICommandHandlerFactory $factory Factory de handlers
     */
    public function __construct(ICommandHandlerFactory $factory)
    {
        $this->_factory = $factory;
        $this->_handlers = [];
    }

    /**
     *  Trouve un handler pour une commande
     *
     * @param ICommand $command Comande dont on cherche le handler
     * @return ICommandHandler[]
     * @throws NoCommandHandlerFound
     */
    public function resolveHandlers(ICommand $command): array
    {
        $handlers = $this->resolveHandlersFromCommandClass(get_class($command));
        if(count($handlers)>0){
            return $handlers;
        }else{
            throw new NoCommandHandlerFound(
                "No command handler found for command ".get_class($command)
            );
        }
    }

    /**
     * @param string $command Classe de la commande à résoudre.
     * @return array
     */
    private function resolveHandlersFromCommandClass(string $command): array
    {
        if(isset($this->_handlers[$command])){
            return $this->_handlers[$command];
        }else{
            $res = [];
            $tmp = explode("\\",$command);
            $className = array_pop($tmp);

            try{
                $res[] = $this->_factory->build(
                    implode('\\',$tmp).'\\handlers\\'.$className.'Handler'
                );
            }catch(\Exception $e){}

            return $res;
        }
    }
}