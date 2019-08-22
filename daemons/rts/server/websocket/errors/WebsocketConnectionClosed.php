<?php

namespace wfw\daemons\rts\server\websocket\errors;

/**
 * Attempting to use a closed websocket connection
 */
class WebsocketConnectionClosed extends WebsocketFailure {}