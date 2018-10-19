<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 19/02/18
 * Time: 07:43
 */

namespace wfw\engine\core\notifier\printer;

use wfw\engine\core\notifier\IMessage;

/**
 * Printer basé sur l'API javascript inclue avec le framework.
 */
final class JSAPIBasedPrinter implements IPrinter
{
    /**
     * Obtient la réprésentation de sortie d'un message
     * @param IMessage $message Message
     * @return string
     */
    public function print(IMessage $message): string
    {
        return
        '<script type="text/javascript">
            window.addEventListener("load",()=>{
                wfw.require("api/ui/notifications");
                wfw.ready(()=>wfw.ui.notifications.display({
                    message : "'.$message.'", icon : "'.$message->getType().'"
                }));
            });
	    </script>';
    }
}