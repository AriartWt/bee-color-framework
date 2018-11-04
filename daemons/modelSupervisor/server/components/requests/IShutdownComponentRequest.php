<?php
namespace wfw\daemons\modelSupervisor\server\components\requests;

/**
 * Demande d'extinction à un composant.
 */
interface IShutdownComponentRequest extends IMSServerComponentRequest,IClientDeniedRequest {}