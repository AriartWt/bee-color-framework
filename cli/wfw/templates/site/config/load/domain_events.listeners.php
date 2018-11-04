<?php

return array_merge(
    require ENGINE.DS."config".DS."default.domain_events.listeners.php",
    require SITE.DS."config".DS."site.domain_events.listeners.php"
);