<?php

return array_merge(
    require dirname(__DIR__,3)."/engine/config/default.command.handlers.php",
    require dirname(__DIR__)."/site.command.handlers.php"
);