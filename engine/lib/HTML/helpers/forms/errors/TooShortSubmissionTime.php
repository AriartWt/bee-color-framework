<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 27/09/18
 * Time: 10:51
 */

namespace wfw\engine\lib\HTML\helpers\forms\errors;

/**
 * Erreur levée lorsqu'une soumission intervient dans un délais trop court
 */
final class TooShortSubmissionTime extends FormValidationPolicyFailure {}