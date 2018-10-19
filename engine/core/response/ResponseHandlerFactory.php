<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 16/02/18
 * Time: 12:00
 */

namespace wfw\engine\core\response;

use Dice\Dice;

/**
 * Crée un ResponseHandler en utilisant Dice.
 */
final class ResponseHandlerFactory implements IResponseHandlerFactory
{
    /**
     * @var Dice $_dice
     */
    private $_dice;

    /**
     * ResponseHandlerFactory constructor.
     *
     * @param Dice $dice Container pour les injection de dépendances du handler.
     */
    public function __construct(Dice $dice)
    {
        $this->_dice = $dice;
    }

    /**
     * Crée un handler $class avec les paramètres $params
     *
     * @param string $class  handler
     * @param array  $params Paramètres de construction du handler
     * @return IResponseHandler
     */
    public function create(string $class, array $params = []): IResponseHandler
    {
        if(is_a($class,IResponseHandler::class,true)){
            /** @var IResponseHandler $handler */
            $handler = $this->_dice->create($class,$params);
            return $handler;
        }else{
            throw new \InvalidArgumentException(
                "$class doesn't implements ".IResponseHandler::class
            );
        }
    }
}