<?php
namespace wfw\daemons\kvstore\server\errors;

/**
 *  Exception levée si l'utilisateur n'est pas connecté et que son action nécessite de l'être ou que l'identifiant de session fourni est incorrect ou périmé.
 */
class MustBeLogged extends AccessDenied{}