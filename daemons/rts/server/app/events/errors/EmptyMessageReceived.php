<?php

namespace wfw\daemons\rts\server\app\events\errors;

/**
 * When an empy message is recieved by an RTSApp
 */
class EmptyMessageReceived extends RTSAppFailure {}