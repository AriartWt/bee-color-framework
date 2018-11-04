<?php

return array_merge(
    require ENGINE . DS . "config" . DS . "default.command.handlers.php",
    require SITE.DS."config".DS."site.command.handlers.php"
);