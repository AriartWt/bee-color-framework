<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/03/18
 * Time: 00:52
 */

namespace wfw\engine\core\security\data;

/**
 * Effectue un OR avec les resultats des règles associées
 */
final class OrRule implements IRule
{
    /**
     * @var IRule[] $_rules
     */
    private $_rules;
    /**
     * @var string $_message
     */
    private $_message;

    /**
     * OrRule constructor.
     *
     * @param null|string  $message  Message en cas d'echec de la règle OR
     * @param IRule[]      ...$rules Régles
     */
    public function __construct(?string $message=null,IRule ...$rules)
    {
        $this->_rules = $rules;
        $this->_message = $message;
    }

    /**
     * @param array $data Données auxquelles appliquer la règle.
     * @return IRuleReport
     */
    public function applyTo(array $data): IRuleReport
    {
        $res = false;
        $errors = [];
        foreach($this->_rules as $rule){
            $report = $rule->applyTo($data);
            $errors[] = $report->errors();
            if($report->satisfied()){
                $res = true;
                break;
            }
        }
        if(!$res){
            return new RuleReport(
                false,
                array_merge_recursive(...$errors),
                $this->_message
            );
        }else{
            return new RuleReport(true);
        }
    }
}