<?php
namespace wfw\engine\lib\PHP\errors;

/**
 *  Levée lorsqu'une méthode est appelée sur un objet qui ne la supporte pas ou d'une manière incorrecte.
 */
class IllegalInvocation extends \Exception {}