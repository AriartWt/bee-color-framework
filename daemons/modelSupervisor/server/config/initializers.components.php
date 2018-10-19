<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 05/01/18
 * Time: 13:21
 */

use wfw\daemons\modelSupervisor\server\components\writer\Writer;
use wfw\daemons\modelSupervisor\server\components\writer\WriterInitializer;

return [
    Writer::NAME => WriterInitializer::class
];