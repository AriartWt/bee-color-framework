<?php

namespace wfw\daemons\rts\server\websocket;


interface IWebsocket {
	public function read():?string;
	public function write():?string;
}