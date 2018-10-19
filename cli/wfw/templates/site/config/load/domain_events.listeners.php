<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 09/02/18
 * Time: 06:22
 */
return array_merge(
    require ENGINE.DS."config".DS."default.domain_events.listeners.php",
    require SITE.DS."config".DS."site.domain_events.listeners.php"
);