<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 09/02/18
 * Time: 06:29
 */
return array_merge(
    require ENGINE . DS . "config" . DS . "default.command.handlers.php",
    require SITE.DS."config".DS."site.command.handlers.php"
);