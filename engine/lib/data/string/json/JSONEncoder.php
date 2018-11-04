<?php
namespace wfw\engine\lib\data\string\json;

/**
 * Utilise la ReflectionClass et ReflectionProperty pour encoder récursivement
 * n'importe quel objet au format JSON.
 * Attention : ne prend pas en considération les références circulaires (boucle infinie)
 * Attention : ne permet pas le rétablissement des références vers un même objet
 */
final class JSONEncoder implements IJSONEncoder {
	/**
	 * @param mixed $data                Données à sérialiser
	 * @param array $skipProperties      Propriétées à omettre sous la forme class=>string[] properties
	 * @param array $transformProperties Callback de transformation d'une propriété sous la forme
	 *                                   class=>[propName=>callable]. Le résultat de callable sera
	 *                                   utilisée comme valeur de propriété
	 * @param array $addProperties       Propriétées à ajouter sous la forme class=>[ property => callable/value ]
	 *                                   Le callable doit etre une fonction prenant l'objet en paramètre.
	 * @param int   $opts
	 * @return string
	 * @throws \ReflectionException
	 */
	public function jsonEncode(
		$data,
		array $skipProperties=[],
		array $transformProperties=[],
		array $addProperties=[],
		int $opts = 0
	):string{
		foreach($skipProperties as $k=>$v){
			$skipProperties[$k]=array_flip($v);
		}
		return json_encode($this->toJsonSerializableStruct(
			$data,
			$skipProperties,
			$transformProperties), $opts
		);
	}

	/**
	 * @param mixed $data Renvoie une structure que la fonction json_encode peut serialiser.
	 * @param array $skipProperties
	 * @param array $transformProperties
	 * @param array $addProperties
	 * @return mixed
	 * @throws \ReflectionException
	 */
	private function toJsonSerializableStruct(
		$data,
		array &$skipProperties=[],
		array &$transformProperties=[],
		array &$addProperties=[]
	){
		if(is_object($data)) return $this->extractProperties(
			$data,
			$skipProperties,
			$transformProperties,
			$addProperties
		);
		else if(is_array($data)){
			$res = [];
			foreach($data as $k=>$v){
				$res[$k] = $this->toJsonSerializableStruct($v);
			}
			return $res;
		}else return $data;
	}

	/**
	 * @param object $data
	 * @param array $skipProperties
	 * @param array $transformProperties
	 * @param array $addProperties
	 * @return array
	 * @throws \ReflectionException
	 */
	private function extractProperties(
		object $data,
		array &$skipProperties=[],
		array &$transformProperties=[],
		array &$addProperties=[]
	):array{
		$res=[];
		$reflect = new \ReflectionClass($data);
		$class = get_class($data);
		$propToAdd = $addProperties[$class] ?? [];
		$propToSkip = $skipProperties[$class] ?? [];
		$propTransformers = $transformProperties[$class] ?? [];

		$objPropToAdd = [];
		$objSkipProperties = [];
		$objTransformProperties = [];
		if($data instanceof IJSONPrintInfos){
			$objPropToAdd = $data->addProperties();
			$objSkipProperties = array_flip($data->skipProperties());
			$objTransformProperties = $data->transformProperties();
		}
		$propToSkip = array_merge($objSkipProperties,$propToSkip);
		$propTransformers = array_merge($objTransformProperties,$propTransformers);
		$propToAdd = array_merge($objPropToAdd,$propToAdd);

		while($reflect){
			foreach($reflect->getProperties() as $p){
				$p->setAccessible(true);
				if(!isset($propToSkip[$p->getName()])){
					if(isset($propTransformers[$p->getName()])){
						if(is_callable($propTransformers[$p->getName()]))
							$res[$p->getName()] = call_user_func_array(
								$propTransformers[$p->getName()],
								[$p->getValue($data)]
							);
						else $res[$p->getName()] = $propTransformers[$p->getName()];
					} else
						$res[$p->getName()] = $this->toJsonSerializableStruct(
							$p->getValue($data)
						);
				}
			}
			$reflect = $reflect->getParentClass();
		}

		foreach($propToAdd as $k=>$v){
			if(is_callable($v)) $res[$k] = call_user_func_array($v,[$data]);
			else $res[$k] = $v;
		}

		return $res;
	}
}