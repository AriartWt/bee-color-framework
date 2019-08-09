<?php
namespace wfw\engine\core\domain\events\store\errors;

/**
 *  Levée lorsqu'une incohérence est détéctée par l'event store
 */
class Inconsistency extends EventStoreFailure {}