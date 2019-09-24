<?php
namespace wfw\daemons\kvstore\server\requests;

/**
 *  Demande l'extinction du serveur.
 */
final class ShutdownKVSServerRequest extends AbstractKVSRequest implements IAdminRequest {}