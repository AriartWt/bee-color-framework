<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/03/18
 * Time: 01:55
 */

namespace wfw\engine\core\security\data;

/**
 * Attend l'inverse d'une règle.
 */
final class NotRule implements IRule
{
    /**
     * @var IRule $_rule
     */
    private $_rule;
    /**
     * @var string $_message
     */
    private $_message;

    /**
     * NotRule constructor.
     *
     * @param IRule  $rule    Régle dont on souhaite obtenir l'inverse
     * @param string $message Message en cas d'echec
     */
    public function __construct(IRule $rule,string $message)
    {
        $this->_rule = $rule;
        $this->_message = $message;
    }

    /**
     * @param array $data Données auxquelles appliquer la règle.
     * @return IRuleReport
     */
    public function applyTo(array $data): IRuleReport
    {
        $report = $this->_rule->applyTo($data);
        if($report->satisfied()){
            return new RuleReport(false,[],$this->_message);
        }else{
            return new RuleReport(true);
        }
    }
}