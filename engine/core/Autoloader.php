<?php 
/**
 *  Namespace général (Web FrameWork)
 * 
 * Namespace général du framework. 
 * 
 * <b style="color:red">/!\ Attention : Aucune classe ne peut y être ajoutée, seule la classe Autoload y est autorisée.</b>
 */
namespace wfw;

/**
 *  Gestion de l'autoload
 * 
 * Gestion de l'autoloading des classes : permet de retrouver les classes à inclure lors des déclarations use ou de leur utilisation.
 */
class Autoloader{

	/**
	 *  Permet d'ajouter la fonction autoload au registre spl_autoload
	 */
	public static function register():void{
		spl_autoload_register(array(__CLASS__,"autoload"));
	}

    /**
     *  Fonction d'autoloading.
     * @param string $className Nom complet de la classe à charger
     */
	public static function autoload(string $className):void{
        if(strpos($className,__NAMESPACE__."\\")!==0){
            if(file_exists(__NAMESPACE__."\\site\\lib\\$className")){
                $className = __NAMESPACE__."\\site\\lib\\$className";
            }else{
                $className = __NAMESPACE__."\\engine\\lib\\$className";
            }
        }
        $expl=explode("\\",$className);
        array_splice($expl,0,1);//on enlève le premier qui est le namespace général
        $file = ROOT.DS.implode(DS,$expl).".php";
        if(file_exists($file)){
            require_once($file);
        }
    }
}

 