<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/03/18
 * Time: 01:39
 */

namespace wfw\engine\core\security\data;

/**
 * Rapport d'execution de régle
 */
final class RuleReport implements IRuleReport
{
    /**
     * @var bool $_satisfied
     */
    private $_satisfied;
    /**
     * @var null|string $_message
     */
    private $_message;
    /**
     * @var array $_errors
     */
    private $_errors;

    /**
     * RuleReport constructor.
     *
     * @param bool        $satisfied True : la régle est satisfaite, false sinon
     * @param array       $errors    (optionnel) Liste des erreurs survenues
     * @param null|string $message   (optionnel) Message global du rapport
     */
    public function __construct(bool $satisfied, array $errors=[], ?string $message=null)
    {
        $this->_errors = $errors;
        $this->_message = $message;
        $this->_satisfied = $satisfied;
    }

    /**
     * @return array Liste des erreurs sous la forme clé=>message
     */
    public function errors(): array
    {
        return $this->_errors;
    }

    /**
     * @return string Message d'erreur global (s'il y en a)
     */
    public function message(): ?string
    {
        return $this->_message;
    }

    /**
     * @return bool True : la régle est satisfaite, false sinon
     */
    public function satisfied(): bool
    {
        return $this->_satisfied;
    }
}