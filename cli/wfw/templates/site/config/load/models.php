<?php
return array_merge(
	require ENGINE."/config/default.models.php",
	require dirname(__DIR__)."/site.models.php"
);