<?php
namespace wfw\engine\core\security\data\rules;

use SoapClient;
use wfw\engine\core\security\data\ForEachFieldRule;

/**
 * Vérifie la validité du numéro de TVA intracommunautaire dans les bases de données de l'UE,
 * en utilisant le service SOAP http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl
 */
final class IsTVAIntracom extends ForEachFieldRule{
	/**
	 * @param mixed $data Donnée sur laquelle appliquer la règle
	 * @return bool
	 */
	protected function applyOn($data): bool {
		if(is_string($data)){
			$data=str_replace(array(" ",",",".","-","_"),'',$data);
			if(strlen($data)>2){
				$countryCode=strtoupper(substr($data,0,2));
				$number=strtoupper(substr($data,2));
				if(preg_match("/^[A-Z]{2}$/",$countryCode)){
					try{
						$client=new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
						$params=array("countryCode"=>$countryCode,"vatNumber"=>$number);
						$result=$client->checkVat($params);
						if($result->valid)return true;
						else return false;
					}catch(\Exception $e){ return false; }
				}else false;
			}else false;
		}else false;
	}
}