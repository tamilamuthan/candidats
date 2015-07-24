<?php
class ClsNaanalLibrary
{
    protected static $arrErrorStatic=array();
    protected static $arrValidate=array();
    private $arrError=array();
    public function __construct() 
    {
        ;
    }
    public static function &getInstance()
    {
        $args=func_get_args();
        $module=array_shift($args);
        
        $path=ClsNaanalApplication::getLibraryModulePath($module);
        if($path===false)
        {
            $success=false;
            self::$arrErrorStatic[]=array("message"=>"Library class {$module} not exist");
            return $success;
        }
        $moduleclass="ClsL".ucfirst($module);
        $class="ClsL".ucfirst($module);
        $path=rtrim($path,"/\\");
        if(file_exists($path."/config.php"))
        {
            include $path."/config.php";
            if(isset($lib_php_dir))
            {
                if(file_exists($lib_php_dir))
                {
                    $lib_php_dir=rtrim($lib_php_dir,"/\\");
                    if(isset($lib_php_file) && is_array($lib_php_file))
                    {
                        foreach($lib_php_file as $tmpArr)
                        {
                            $external_file=$tmpArr["file"];
                            //$external_class=$tmpArr["class"];
                            $external_include_file="{$lib_php_dir}/{$external_file}";
                            if(!file_exists($external_include_file))
                            {
                                die("Library {$module} include file '{$external_include_file}' not exist");
                            }
                            include_once($external_include_file);
                        }
                    }
                    else if(!isset($lib_php_file))
                    {
                        die("Library {$module}'s include file has to be defined. 
<pre>Ex: \$lib_php_file=array
(
'include'=>array('file'=>'include php file','class'=>'php class to include')
);</pre>");
                    }
                    else
                    {
                        die("Library {$module}'s include file must be an array. 
<pre>Ex: \$lib_php_file=array
(
'include'=>array('file'=>'include php file','class'=>'php class to include')
);</pre>");
                    }
                }
                else
                {
                    die("Library {$module} missing at {$lib_php_dir}");
                }
            }
        }
        if(file_exists($path."/utils.php"))
        {
            include_once($path."/utils.php");
        }
        $include_file=$path."/".$class.".php";
        self::validate($include_file,$class);
        if(self::$arrValidate)
        {
            print_r(self::$arrValidate);exit;
        }
        
        include_once $include_file;
        
        if($args)
        {
            $arrArg=array();
            foreach($args as $ind=>$arg)
            {
                $arrArg["key{$ind}"]=$arg;
            }
            extract($arrArg);
            $class=$class.'(';
            foreach($args as $ind=>$arg)
            {
                if($ind===0)
                {
                    $class=$class."\$key{$ind}";
                }
                else
                {
                    $class=$class.",\$key{$ind}";
                }
            }
            $class=$class.')';
        }
        eval('$obj=new '.$class.';');
        return $obj;
    }
    public static function validate($include_file,$class)
    {
        $arrNotAllowedToken=array
        (
            T_PRINT,T_ECHO
        );
        $arrFilterElem=array
        (
            "print_r"
        );
        $arrFilterElemPref=array
        (
            "mysql_"
        );
        $arrToken=token_get_all(file_get_contents($include_file));
        $count=count($arrToken);
        $isExtends=false;
        $isConstructorDefined=false;
        for($i=0;$i<$count;$i++)
        {
            $token=$arrToken[$i];
            if(!is_array($token)) continue;
            if($token[1]=="extends")
            {
                $isExtends=true;
                for($j=$i+1;$j<$count;$j++)
                {
                    $tokennext=$arrToken[$j];
                    if(!is_array($tokennext)) continue;
                    if($tokennext[1]=="__construct")
                    {
                        $isConstructorDefined=true;
                        break;
                        $i=$j+1;
                    }
                }
                //if($isConstructorDefined) break;
            }
            if($token[0]===T_STRING)
            {
                foreach($arrFilterElemPref as $elemPref)
                {
                    if(strpos($token[1], $elemPref)===0)
                    {
                        self::$arrValidate["ElementPrefNotAllowed"][]=array("class"=>$class,"type"=>"library","message"=>"Element ({$token[1]}) with prefix ($elemPref) not allowed");
                    }
                }
                if(in_array($token[1], $arrFilterElem))
                {
                    self::$arrValidate["ElementNotAllowed"][]=array("class"=>$class,"type"=>"library","message"=>"Element ({$token[1]}) not allowed");
                }
            }
        }
        if(!$isConstructorDefined)
        {
            self::$arrValidate["MethodNotFound"]["constructor"][]=array("class"=>$class,"method"=>"__construct","type"=>"library","message"=>"Constructor for Class ({$class}) not found");
        }
        if(!$isExtends)
        {
            self::$arrValidate["ParentNotFound"][]=array("class"=>$class,"type"=>"library","message"=>"Class ({$class}) not found");
        }
        $naanal_error=errMsg();
        $isOtherError=false;
        foreach($arrToken as $ind=>$data)
        {
            if(in_array($data[0], $arrNotAllowedToken))
            {
                $tokenName=  token_name($data[0]);
                self::$arrValidate["ElementNotAllowed"][]=array("file"=>$include_file,"element"=>$tokenName,"type"=>"library","message"=>"File: {$include_file}, Page: {$include_file}, Line No.: {$data[2]}, Element Type : {$data[0]}({$tokenName}) Element '{$data[1]}' not allowed inside library Module Class. ");
            }
        }
    }
    public static function getErrorStatic()
    {
        return self::$arrErrorStatic;
    }
    public function getError()
    {
        return $this->$arrError;
    }
}
?>