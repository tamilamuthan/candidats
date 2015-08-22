<?php
/**
 * Baseclass for controller 
 */
class ClsNaanalController
{
    public $mode="create";
    protected $action=false;
    protected $id=null;
    protected $table="";
    protected $module="";
    protected $switch="";
    public $objParentModule=null;
    protected $arrConfigVar=array();
    protected $urlModuleParam="page";
    protected $urlActionParam="action";

    protected $database="";

    public $objPDO=null;
    public $controlpanel=null;
    protected $redirect=null; 
    public $wrapper=null;
    public $ui=null;
    public $isAddNew=true;

    private $arrField=array();

    public $itemperpage=20;
    public $arrOrderBy=array();

    //protected $isPartionExist=false;

    protected $fldID="id";
    
    protected $objNaanalRequest=null;
    protected $objNaanalPost=null;
    protected $objNaanalGet=null;
    
    protected $arrNaanalData=array();
    protected $arrAdditionalInput=array();
    
    protected $form=null;
    
    protected $arrWhere=array();
    ///global template variable holder
    private $arrTplVar=array();
    ///holder of html header datas
    private $arrHeaderData=array();
    ///sub template holder for processing in ClsNaanalModule
    private $arrSubTemplate=array();
    
    protected $modulePath="";
    
    public static $arrErrorStatic=array();
    
    protected $objModuleRequest=null;
    protected $objViewer=null;

    function __construct($module=false)
    {
        $this->urlModuleParam=ClsNaanalRequest::getInstance()->getUrlModuleParam();
        $this->urlActionParam=ClsNaanalRequest::getInstance()->getUrlActionParam();
        if($module!==false)
        {
            $this->module=$module;
        }
    }
    public function modifyAction($action)
    {
        $this->action=$action;
    }
    public function setSwitch($switch)
    {
        $this->switch=$switch;
    }
    public function modifyModule($module)
    {
        $this->module=$module;
    }
    public function modifySwitch($switch)
    {
        $this->switch=$switch;
    }
    public function getModifiedAction()
    {
        return $this->action;
    }
    public function loadTemplate($arrTpl,$templatefile)
    {
        //$template = new Template();

        if (isset($_SESSION['CATS']) && !empty($_SESSION['CATS']))
        {
            /* Get the current user's user ID. */
            $userID = $_SESSION['CATS']->getUserID();

            /* Get the current user's site ID. */
            $siteID = $_SESSION['CATS']->getSiteID();

            /* Get the current user's access level. */
            $accessLevel = $_SESSION['CATS']->getAccessLevel();

            /* All templates have an access level if we have a session. */
            $this->_template->assign('accessLevel', $accessLevel);
            foreach($arrTpl as $var=>$data)
            {
                $this->_template->assign($var, $data);
            }
            $this->_template->display("./modules/mailclient/{$templatefile}.php");
        }
    }
}