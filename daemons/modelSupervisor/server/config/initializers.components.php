<?php
use wfw\daemons\modelSupervisor\server\components\writer\Writer;
use wfw\daemons\modelSupervisor\server\components\writer\WriterInitializer;

return [
	Writer::NAME => WriterInitializer::class
];