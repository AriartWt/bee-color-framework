<?php
return array_merge(
	require dirname(__DIR__,3)."/engine/config/default.models.php",
	require dirname(__DIR__)."/site.models.php"
);