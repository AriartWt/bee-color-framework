<?php
namespace wfw\engine\lib\network\socket\protocol;

use wfw\engine\lib\network\socket\errors\SocketFailure;

/**
 *  Protocol de communication entre sockets par défaut.
 */
class StreamProtocol implements ISocketProtocol {
	/** Taille de l'entête*/
	private const HEADER_LENGTH = 10;
	/** Taille des chunks */
	private const CHUNK_SIZE = 16384;

	/**
	 * Lis des données sur la socket
	 *
	 * @param resource $socket socket à lire
	 *
	 * @return string
	 */
	public function read($socket):string{
		$header = fread($socket,self::HEADER_LENGTH);
		if(is_bool($header)) throw new SocketFailure(
			null,socket_last_error($socket)
		);
		$totalSize = intval($header);
		$readed = 0;
		$data = '';

		while($totalSize>0){
			$toRead = (($totalSize>self::CHUNK_SIZE)?self::CHUNK_SIZE:$totalSize);
			$totalSize -= $toRead;
			$readed += $toRead;
			$data.=fread($socket,$toRead);
		}
		return $data;
	}

	/**
	 * Ecrit des données dans la socket
	 * @param resource $socket Socket d'écriture
	 * @param string   $str    Données à écrire
	 */
	public function write($socket,string $str){
		$totalSize = strlen($str);
		$dataLength = (string)$totalSize;
		while(strlen($dataLength)<self::HEADER_LENGTH){
			$dataLength = "0$dataLength";
		}
		if(is_bool(fwrite($socket,$dataLength,self::HEADER_LENGTH)))
			throw new SocketFailure(null,socket_last_error($socket));
		$offset = 0;
		while($totalSize > 0){
			$toWrite = (($totalSize>self::CHUNK_SIZE)?self::CHUNK_SIZE:$totalSize);
			fwrite($socket,substr($str,$offset,$toWrite),$toWrite);
			$offset += $toWrite;
			$totalSize -= $toWrite;
		}
	}
}