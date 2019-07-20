<?php

return array_merge(
    require dirname(__DIR__,3)."/engine/config/default.domain_events.listeners.php",
    require dirname(__DIR__)."/site.domain_events.listeners.php"
);