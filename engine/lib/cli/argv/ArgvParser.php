<?php
namespace wfw\engine\lib\cli\argv;

/**
 *  Parser d'arguments
 */
class ArgvParser implements IArgvParser {
	/** @var ArgvOptMap */
	private $_opts;

	/**
	 *  ArgvParser constructor.
	 *
	 * @param ArgvOptMap $opts Options attendues
	 */
	public function __construct(ArgvOptMap $opts) {
		$this->_opts = $opts;
	}

	/**
	 *  Parse un tableau d'arguments
	 *
	 * @param array     $argv Arguments
	 *
	 * @return ArgvParserResult
	 */
	public function parse(array $argv): ArgvParserResult {
		$values=[];
		$usedOptions = [];
		for($i=0; $i<count($argv) ;$i++){
			if($this->_opts->exists($argv[$i])){
				$currentOpt = $this->_opts->get($argv[$i]);
				$values[$currentOpt->getName()] = [];
				$usedOptions[]=$currentOpt->getName();

				$i++;
				if(is_null($currentOpt->getLength())){
					//On lis tant qu'on rencontre pas une autre option, ou la fin des arguments
					while($i<count($argv) && !$this->_opts->exists($argv[$i])){
						if($currentOpt->validates($argv[$i])){
							$values[$currentOpt->getName()][]=$argv[$i];
						}else{
							throw new \InvalidArgumentException(
								$currentOpt->getValidatorFailMessage([
									$argv[$i],
									$currentOpt->getName(),
									$currentOpt->getLength(),
									$currentOpt->getDescription()
								])
							);
						}
						$i++;
					}
				}else{
					$nbReader = 0;
					while($nbReader < $currentOpt->getLength()){
						if(($i>=count($argv) || $this->_opts->exists($argv[$i]))){
							throw new \InvalidArgumentException("\nCommand ".$currentOpt->getName()
								." expects exactly ".$currentOpt->getLength()
								." arguments : $nbReader given !\n"
							);
						}else{
							if($currentOpt->validates($argv[$i])){
								$values[$currentOpt->getName()][]=$argv[$i];
							}else{
								throw new \InvalidArgumentException(
									$currentOpt->getValidatorFailMessage([
										$argv[$i],
										$currentOpt->getName(),
										$currentOpt->getLength(),
										$currentOpt->getDescription()
									])
								);
							}
						}
						$i++;
						$nbReader++;
					}
					if($nbReader!==$currentOpt->getLength()){
						throw new \InvalidArgumentException("\nCommand ".$currentOpt->getName()
							." expects exactly ".$currentOpt->getLength()
							." arguments : $nbReader given !\n"
						);
					}
				}
				$i--;
			}
		}
		$missing = [];
		foreach($this->_opts as $opt){
			if($this->_opts->getLength()>0){
				if(is_bool(array_search($opt->getName(),$usedOptions))){
					/** @var ArgvOpt $opt */
					if(!$opt->isOptionnal()){
						$missing[] = $opt->getName()." : ".$opt->getDescription();
					}
				}
			}
		}
		if(isset($values["--help"])){
			throw new \InvalidArgumentException();
		}
		if(count($missing)>0){
			throw new \InvalidArgumentException(
				"\nSome required args are missing : \n".implode("\n",$missing)."\n"
			);
		}

		return new ArgvParserResult($values);
	}

	/**
	 * @return ArgvOptMap
	 */
	public function getOptMap(): ArgvOptMap {
		return $this->_opts;
	}
}