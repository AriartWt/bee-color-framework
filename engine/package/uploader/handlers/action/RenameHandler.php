<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 08/05/18
 * Time: 10:52
 */

namespace wfw\engine\package\uploader\handlers\action;

use wfw\engine\core\action\IAction;
use wfw\engine\core\conf\IConf;
use wfw\engine\core\request\IRequest;
use wfw\engine\core\request\IRequestData;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\responses\ErrorResponse;
use wfw\engine\core\response\responses\Response;
use wfw\engine\package\uploader\security\data\RenamePathRule;

/**
 * Renome un fichier ou un dossier
 */
final class RenameHandler extends UploadHandler
{
    /**
     * @var RenamePathRule $_rule
     */
    private $_rule;

    /**
     * RenameHandler constructor.
     *
     * @param IConf          $conf Configurations du site
     * @param RenamePathRule $rule Régle de validation
     */
    public function __construct(IConf $conf,RenamePathRule $rule) {
        parent::__construct($conf, null);
        $this->_rule = $rule;
    }

    /**
     * @param IAction $action Action
     * @return IResponse Réponse
     */
    public function handle(IAction $action): IResponse
    {
        if($action->getRequest()->isAjax() && $action->getRequest()->getMethod()===IRequest::POST){
            $data = $action->getRequest()->getData()->get(IRequestData::POST,true);
            $res = $this->_rule->applyTo($data);
            if($res->satisfied()){
                try{
                    $oldPaths = $data["oldPaths"];
                    $newPaths = $data["newPaths"];
                    if(count($oldPaths)===count($newPaths) && count($oldPaths) > 0){
                        for($i=0;$i<count($oldPaths);$i++){
                            rename(
                                $this->realPath(strip_tags($oldPaths[$i])),
                                $this->realPath(strip_tags($newPaths[$i]))
                            );
                        }
                        return new Response();
                    }else return new ErrorResponse("201","Elems to rename list doesn't match new name list !");
                }catch(\InvalidArgumentException $e){
                    return new ErrorResponse(201,$e->getMessage());
                }
            }else return new ErrorResponse(201,$res->message(),$res->errors());
        }else return new ErrorResponse(404,"Page not found");
    }
}