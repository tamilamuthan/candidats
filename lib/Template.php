<?php
/**
 * CATS
 * Template Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: Template.php 3587 2007-11-13 03:55:57Z will $
 */

/* 
 * CandidATS
 * Document to Text Conversion Library
 *
 * Copyright (C) 2014 - 2015 Auieo Software Private Limited, Parent Company of Unicomtech.
 * 
 * This Modified Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

/**
 *	Template Library
 *	@package    CATS
 *	@subpackage Library
 */
class Template
{
    private $_templateFile;
    private $_filters = array();
    private $isRendered=false;

    /**
     * Prints $string with all html special characters converted to &codes;.
     *
     * Ex: 'If x < 2 & x > 0, x = 1.' -> 'If x &lt; 2 &amp; x &gt; 0, x = 1.'.
     *
     * @param string input
     * @return void
     */
    public function _($string)
    {
        echo(htmlspecialchars($string));
    }
    
    public function isRendered()
    {
        return $this->isRendered;
    }

    /**
     * Assigns the specified property value to the specified property name
     * for access within the template.
     *
     * @param string property name
     * @param mixed property value
     * @return void
     */
    public function assign($propertyName, $propertyValue)
    {
        $this->$propertyName = $propertyValue;
    }

    /**
     * Assigns the specified property value to the specified property name,
     * by reference, for access within the template.
     *
     * @param string property name
     * @param mixed property value
     * @return void
     */
    public function assignByReference($propertyName, &$propertyValue)
    {
        $this->$propertyName =& $propertyValue;
    }

    /**
     *  TODO: Document me.
     */
    public function addFilter($code)
    {
        $this->_filters[] = $code;
    }
    /**
     * return only capital letter variables with AUIEO keyword
     * @param type $__AUIEO__TEMPLATE__FILE
     * @return type
     */
    private function &loadTemplateVars($__AUIEO__TEMPLATE__FILE,$__AUIEO__OTHER__VAR=false)
    {
        if(!file_exists($__AUIEO__TEMPLATE__FILE)) return array();
        if($__AUIEO__OTHER__VAR!==false)
        {
            extract($__AUIEO__OTHER__VAR);
        }
        include $__AUIEO__TEMPLATE__FILE;
        $arrVar=get_defined_vars();
        unset($arrVar[$__AUIEO__TEMPLATE__FILE]);
        $arrVarNew=array();
        foreach($arrVar as $var=>$data)
        {
            $tmpVar = strtoupper($var);
            if(strpos($tmpVar, "AUIEO")===false) continue;
            if(isset($$tmpVar))
            {
                $arrVarNew[$tmpVar]=$data;
            }
        }
        return $arrVarNew;
    }
    /**
     * 
     * @param type $template
     * @param type $var - template variale where the processed content stored
     * @return type
     */
    public function subTemplate($template,$var,$arrAuieoTplVar=array())
    {
            /* File existence checking. */
            if(!file_exists($template))
            {
                $arrPathInfo=pathinfo($template);
                if(file_exists("auieo/common/template/{$arrPathInfo["basename"]}"))
                {
                    $template="auieo/common/template/{$arrPathInfo["basename"]}";
                }
            }
            $file = realpath('./' . $template);
            if (!$file)
            {
                echo 'Template error: File \'', $template, '\' not found.', "\n\n";
                return;
            }
            /**
            * for handing comment in html template. usage is {$_("This is comment")}
            */
           $_=function($comment)
           {
               return "";
           };

            /* Include the template, with output buffering on, and echo it. */
            $arrPathInfo=pathinfo($file);
            if($arrPathInfo["extension"]=="php" && (file_exists("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.html") || file_exists("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.htm")))
            {
               $otherVar = $arrAuieoTplVar;
               $arrTplVar=$this->loadTemplateVars($file,$otherVar);
               extract($arrTplVar);
                if(file_exists("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.html"))
                    $_AUIEO_TEMPLATE_CONTENT=file_get_contents("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.html");
                else
                    $_AUIEO_TEMPLATE_CONTENT=file_get_contents("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.htm");
                try
                {
                    ob_start();
                    $AUIEO_MODULE_CONTENT="";
                    eval('echo <<< EOT
            '.$_AUIEO_TEMPLATE_CONTENT.'
EOT;
');
                    $html = ob_get_clean();
                }
                catch(Exception $e)
                {
                    trace($e);
                }
            }
            else
            {
                ob_start();
                include($this->_templateFile);
                $html = ob_get_clean();
            }
            $this->assign($var, $html);
    }

    /**
     * Evaluates a template file. All assignments (see the Template::assign()
     * and Template::assignByReference() methods) must be made before calling
     * this method. The template filename is relative to index.php.
     *
     * @param string template filename
     * @return void
     */
    public function display($template,$objView=false,$isTheme=true)
    {
        $this->isRendered=true;
        $arrAuieoTplVar=array();
        if($objView!==false)
        {
            $arrAuieoTplVar=$objView->render();
        }
            /* File existence checking. */
            if(!file_exists($template))
            {
                $arrPathInfo=pathinfo($template);
                if(file_exists("auieo/template/{$arrPathInfo["basename"]}"))
                {
                    $template="auieo/template/{$arrPathInfo["basename"]}";
                }
            }
            $file = realpath('./' . $template);
            if (!$file)
            {
                echo 'Template error: File \'', $template, '\' not found.', "\n\n";
                return;
            }

            $this->_templateFile = $file;

            /* We don't want any variable name conflicts here. */
            unset($file, $template);

            /**
            * for handing comment in html template. usage is {$_("This is comment")}
            */
           $_=function($comment)
           {
               return "";
           };

            /* Include the template, with output buffering on, and echo it. */
            $arrPathInfo=pathinfo($this->_templateFile);
            if($arrPathInfo["extension"]=="php" && (file_exists("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.html") || file_exists("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.htm")))
            {
               $otherVar = $arrAuieoTplVar;
               $arrTplVar=$this->loadTemplateVars($this->_templateFile,$otherVar);
               extract($arrTplVar);
               //trace($arrPathInfo);
                if(file_exists("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.html"))
                    $_AUIEO_TEMPLATE_CONTENT=file_get_contents("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.html");
                else
                    $_AUIEO_TEMPLATE_CONTENT=file_get_contents("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.htm");
                try
                {
                    ob_start();
                    $AUIEO_MODULE_CONTENT="";
                    eval('echo <<< EOT
            '.$_AUIEO_TEMPLATE_CONTENT.'
EOT;
');
                    $html = ob_get_clean();
                }
                catch(Exception $e)
                {
                    trace($e);
                }
            }
            else
            {
                ob_start();
                include($this->_templateFile);
                $html = ob_get_clean();
            }
            if (strpos($html, '<!-- NOSPACEFILTER -->') === false && strpos($html, 'textarea') === false)
            {
                $html = preg_replace('/^\s+/m', '', $html);
            }

            foreach ($this->_filters as $filter)
            {
                eval($filter);
            }
            if($isTheme===false)
            {
                echo $html;
            }
            else
                echo $this->loadTheme(array("out"=>$html,"AUIEO_MODULE_CONTENT"=>"","AUIEO_VIEW_OBJECT"=>$objView));
    }
    
    private function loadTheme($_AUIEO_ARR_THEME_VAR)
    {
        Logger::getLogger("AuieoATS")->info("Template:loadTheme entry");
        if($_SERVER["REQUEST_URI"]=="/demo/careers/")
        {
            $AUIEO_THEME_MODULE="careers";
        }
        else if(defined("AUIEO_CAREER_PAGE"))
        {
            $AUIEO_THEME_MODULE="careers";            
        }
        else if(isset($_REQUEST["m"]) && $_REQUEST["m"]=="careers")
        {
            $AUIEO_THEME_MODULE="careers";
        }
        else if(!$_SESSION['CATS']->isLoggedIn())
        {
            $AUIEO_THEME_MODULE="login";
        }
        else
        {
            $AUIEO_THEME_MODULE=isset($_REQUEST["m"])?$_REQUEST["m"]:"index";
        }
        
        $objModule=new ClsAuieoModule($AUIEO_THEME_MODULE,isset($_REQUEST["a"])?$_REQUEST["a"]:"create");
        $actionTheme=$objModule->getConfigVar("actions");
        if($actionTheme)
        {
            $AUIEO_THEME_MODULE=$actionTheme;
        }
        if(!file_exists("themes/default/{$AUIEO_THEME_MODULE}.php"))
        {
            $AUIEO_THEME_MODULE="index";
        }
        extract($_AUIEO_ARR_THEME_VAR);
        unset($_AUIEO_ARR_THEME_VAR);
        
        $HTML_ENCODING=HTML_ENCODING;
        
        $AUIEO_USER_NAME     = $_SESSION['CATS']->getUsername();
        $AUIEO_SITE_NAME     = $_SESSION['CATS']->getSiteName();
        $AUIEO_FULL_NAME     = $_SESSION['CATS']->getFullName();
        $AUIEO_INDEX_NAME    = CATSUtility::getIndexName();
        
        $_AUIEO_MODULE=isset($_GET["m"])?$_GET["m"]:"home";
        
        if (strpos($AUIEO_USER_NAME, '@'.$_SESSION['CATS']->getSiteID()) !== false &&
            substr($AUIEO_USER_NAME, strpos($username, '@'.$_SESSION['CATS']->getSiteID())) ==
            '@'.$_SESSION['CATS']->getSiteID() )
        {
           $AUIEO_USER_NAME = str_replace('@'.$_SESSION['CATS']->getSiteID(), '', $AUIEO_USER_NAME);
        }

        $ACCESS_LEVEL_SA_GREATER="";
        if ($_SESSION['CATS']->getAccessLevel() >= ACCESS_LEVEL_SA)
        {
            $ACCESS_LEVEL_SA_GREATER = "&nbsp;<span style='font-weight:bold;'>Administrator</span>\n";
        }

        $AUIEO_NOTICE="";
        /* Disabled notice */
        if (!$_SESSION['CATS']->accountActive())
        {
            $AUIEO_NOTICE = "<span style='font-weight:bold;'>Account Inactive</span><br />\n";
        }
        else if ($_SESSION['CATS']->getAccessLevel() == ACCESS_LEVEL_READ)
        {
            $AUIEO_NOTICE = "<span>Read Only Access</span><br />\n";
        }
        
        ob_start();
        $forceHighlight = '';

        $modules = ModuleUtility::getModules();
        if($_SESSION["CATS"]->getSiteID()>0)
        {
            $objPRGManagement=PRGManagement::getInstance();
        }
        foreach ($modules as $moduleName => $parameters)
        {
            if($_SESSION["CATS"]->getSiteID()>0)
            {
                $permit=$objPRGManagement->isModulePermitted($moduleName);
                if($permit===false)
                {
                    continue;
                }
            }
           $tabText = $parameters[1];

           /* Don't display a module's tab if $tabText is empty. */
           if (empty($tabText))
           {
               continue;
           }

           /* If name = Companies and HR mode is on, change tab name to My Company. */
           if ($_SESSION['CATS']->isHrMode() && $tabText == 'Companies')
           {
               $tabText = 'My Company';
           }

           /* Allow a hook to prevent a module from being displayed. */
           $displayTab = true;

           if (!eval(Hooks::get('TEMPLATE_UTILITY_EVALUATE_TAB_VISIBLE'))) return;

           if (!$displayTab)
           {
               continue;
           }

           /* Inactive Tab? */
           if ( !isset($this->active) || empty($this->active) || $moduleName != $this->active->getModuleName())
           {
               if ($moduleName == $forceHighlight)
               {
                   $className = 'active';
               }
               else
               {
                   $className = 'inactive';
               }

               $alPosition = strpos($tabText, "*al=");
               if ($alPosition === false)
               {
                   echo '<li><a class="', $className, '" href="', $AUIEO_INDEX_NAME,
                        '?m=', $moduleName, '">', $tabText, '</a></li>', "\n";
               }
               else
               {
                    $al = substr($tabText, $alPosition + 4);
                    if ($_SESSION['CATS']->getAccessLevel() >= $al ||
                        $_SESSION['CATS']->isDemo())
                    {
                       echo '<li><a class="', $className, '" href="', $indexName, '?m=', $moduleName, '">',
                            substr($tabText, 0, $alPosition), '</a></li>', "\n";
                   }
               }

               continue;
           }

           $alPosition = strpos($tabText, "*al=");
           if ($alPosition !== false)
           {
               $tabText = substr($tabText, 0, $alPosition);
           }

           /* Start the <li> block for the active tab. The secondary <ul>
            * for subtabs MUST be contained within this block. It is
            * closed after subtabs are printed. */
           echo '<li>';

           echo '<a class="active" href="', $AUIEO_INDEX_NAME, '?m=', $moduleName,
                '">', $tabText, '</a>', "\n";

           $subTabs = $this->active->getSubTabs($modules);
           if ($subTabs)
           {
               echo '<ul id="secondary">';

               foreach ($subTabs as $subTabText => $link)
               {
                   if (isset($this->subActive) && $subTabText == $this->subActive)
                   {
                       $style = "color:#cccccc;";
                   }
                   else
                   {
                       $style = "";
                   }

                   /* Check HR mode for displaying tab. */
                   $hrmodePosition = strpos($link, "*hrmode=");
                   if ($hrmodePosition !== false)
                   {
                       /* Access level restricted subtab. */
                       $hrmode = substr($link, $hrmodePosition + 8);
                       if ((!$_SESSION['CATS']->isHrMode() && $hrmode == 0) ||
                           ($_SESSION['CATS']->isHrMode() && $hrmode == 1))
                       {
                           $link =  substr($link, 0, $hrmodePosition);
                       }
                       else
                       {
                           $link = '';
                       }
                   }

                   /* Check access level for displaying tab. */
                   $alPosition = strpos($link, "*al=");
                   if ($alPosition !== false)
                   {
                       /* Access level restricted subtab. */
                       $al = substr($link, $alPosition + 4);
                       if ($_SESSION['CATS']->getAccessLevel() >= $al ||
                           $_SESSION['CATS']->isDemo())
                       {
                           $link =  substr($link, 0, $alPosition);
                       }
                       else
                       {
                           $link = '';
                       }
                   }

                   $jsPosition = strpos($link, "*js=");
                   if ($jsPosition !== false)
                   {
                       /* Javascript subtab. */
                       echo '<li><a href="', substr($link, 0, $jsPosition), '" onclick="',
                            substr($link, $jsPosition + 4), '" style="'.$style.'">', $subTabText, '</a></li>', "\n";
                   }

                   /* A few subtabs have special logic to decide if they display or not. */
                   /* FIXME:  Put the logic for these somewhere else.  Perhaps the definitions of the subtabs
                              themselves should have an eval()uatable rule?
                              Brian 6-14-07:  Second.  */
                   else if (strpos($link, 'a=internalPostings') !== false)
                   {
                       /* Default company subtab. */
                       include_once('./lib/Companies.php');

                       $companies = new Companies($_SESSION['CATS']->getSiteID());
                       $defaultCompanyID = $companies->getDefaultCompany();
                       if ($defaultCompanyID !== false)
                       {
                           echo '<li><a href="', $link, '" style="'.$style.'">', $subTabText, '</a></li>', "\n";
                       }
                   }
                   else if (strpos($link, 'a=administration') !== false)
                   {
                       /* Administration subtab. */
                       if ($_SESSION['CATS']->getRealAccessLevel() >= ACCESS_LEVEL_DEMO)
                       {
                           echo '<li><a href="', $link, '" style="'.$style.'">', $subTabText, '</a></li>', "\n";
                       }
                   }
                   else if (strpos($link, 'a=customizeEEOReport') !== false)
                   {
                       /* EEO Report subtab.  Shouldn't be visible if EEO tracking is disabled. */
                       $EEOSettings = new EEOSettings($_SESSION['CATS']->getSiteID());
                       $EEOSettingsRS = $EEOSettings->getAll();

                       if ($EEOSettingsRS['enabled'] == 1)
                       {
                           echo '<li><a href="', $link, '" style="'.$style.'">', $subTabText, '</a></li>', "\n";
                       }
                   }


                   /* Tab is ok to draw. */
                   else if ($link != '')
                   {
                       /* Normal subtab. */
                       echo '<li><a href="', $link, '" style="'.$style.'">', $subTabText, '</a></li>', "\n";
                   }
               }

               if (!eval(Hooks::get('TEMPLATE_UTILITY_DRAW_SUBTABS'))) return;

               echo '</ul>';
           }

           echo '</li>';
        }

        $_AUIEO_TABS=  ob_get_clean();
$MRU = $_SESSION['CATS']->getMRU()->getFormatted();
                $indexName = CATSUtility::getIndexName();
        
        $AUIEO_PREFIX="";
        if(isset($_REQUEST["m"]) && $_REQUEST["m"]=="careers")
        {
            $AUIEO_PREFIX="../";
        }
        $systemInfo = new SystemInfo();
        $systemInfoData = $systemInfo->getSystemInfo();
        $AUIEO_DOWNLOAD_LATEST="";
        if (isset($systemInfoData['available_version']) &&
            $systemInfoData['available_version'] > CATSUtility::getVersionAsInteger() &&
            isset($systemInfoData['disable_version_check']) &&
            !$systemInfoData['disable_version_check'] &&
            $_SESSION['CATS']->getAccessLevel() >= ACCESS_LEVEL_SA)
        {
            $AUIEO_DOWNLOAD_LATEST = "<a href='http://www.catsone.com/download.php' target='catsdl'>A new CATS version is available!</a><br />";
        }
        $AUIEO_RECENT="";
        if (!empty($MRU))
        {
            $AUIEO_RECENT = "<span class='MRUTitle'>Recent:&nbsp;</span>&nbsp;{$MRU}";
        }
        else
        {
            $AUIEO_RECENT = '<span class="MRUTitle"></span>&nbsp;';
        }
        $AUIEO_HAS_USER_CATEGORY="";
        //FIXME:  Abstract into a hook.
        $AUIEO_TAG_SEARCH="";
        if ($_SESSION['CATS']->hasUserCategory('msa'))
        {
            $AUIEO_HAS_USER_CATEGORY = "<input type='hidden' name='m' value='asp' />
                <input type='hidden' name='a' value='aspSearch' />
                <span class='quickSearchLabel' id='quickSearchLabel>ASP Search:</span>&nbsp;";
        }
        else
        {
            $objDB=  DatabaseConnection::getInstance();
            $AUIEO_HAS_USER_CATEGORY = "<input type='hidden' name='m' value='home' />
                <input type='hidden' name='a' value='quickSearch' />
                <span class='quickSearchLabel' id='quickSearchLabel'>Quick Search:</span>&nbsp;";
        }


        $wildCardString = '';
        /* Get the formatted MRU list from Session. */
        
        $pageTitle = pageTitle();
        $headIncludes = pageHeaderInclude();
        $AUIEO_PAGE_START="";
        ob_start();
        //TemplateUtility::_printCommonHeader($pageTitle, $headIncludes);

        if (!is_array($headIncludes))
        {
            $headIncludes = array($headIncludes);
        }

        $siteID = $_SESSION['CATS']->getSiteID();

        /* This prevents caching problems when SVN updates are preformed. */
        if ($_SESSION['CATS']->getCachedBuild() > 0)
        {
            $javascriptAntiCache = '?b=' . $_SESSION['CATS']->getCachedBuild();
        }
        else
        {
            $javascriptAntiCache = '?v=' . CATSUtility::getVersionAsInteger();
        }

        $headIncludes[] = 'main.css';

        foreach ($headIncludes as $key => $filename)
        {
            /* Done manually to prevent a global dependency on FileUtility. */
            if ($filename == 'tinymce')
            {
                echo ('<script language="javascript" type="text/javascript" src="lib/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>'."\n".
                      '<script language="javascript" type="text/javascript">tinyMCE.init({
                            mode : "specific_textareas",
                            editor_selector : "mceEditor",
                            width : "100%",
                                theme : "advanced",
                                theme_advanced_buttons1 : "bold,italic,strikethrough,separator,bullist,numlist,outdent,indent,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,link,unlink,separator,underline,forecolor,separator,removeformat,cleanup,separator,charmap,separator,undo,redo",
                                theme_advanced_buttons2 : "",
                                theme_advanced_buttons3 : "",
                                language : "en",
                                theme_advanced_toolbar_location : "top",
                                theme_advanced_toolbar_align : "left",
                                theme_advanced_resizing : true,
                                browsers : "msie,gecko,opera,safari",
                                dialog_type : "modal",
                                theme_advanced_resize_horizontal : false,
                                convert_urls : false,
                                relative_urls : false,
                                remove_script_host : false,
                                force_p_newlines : false,
                                force_br_newlines : true,
                                convert_newlines_to_brs : false,
                                remove_linebreaks : false,
                                fix_list_elements : true
                        });</script>'."\n");
            }
            else
            {

                $extension = substr($filename, strrpos($filename, '.') + 1);

                $filename .= $javascriptAntiCache;

                if ($extension == 'js')
                {
                    echo '<script type="text/javascript" src="', $filename, '"></script>', "\n";
                }
                else if ($extension == 'css')
                {
                    echo '<style type="text/css" media="all">@import "', $filename, '";</style>', "\n";
                }
            }
        }


        $AUIEO_PAGE_START=  ob_get_clean();

        //ob_start();
        $AUIEO_LOAD_TIME = $_SESSION['CATS']->getExecutionTime();

        $AUIEO_CANDIDATS_VERSION=CANDIDATS_VERSION;
        
        include("themes/default/{$AUIEO_THEME_MODULE}.php");
        /**
        * for handing comment in html template. usage is {$_("This is comment")}
        */
       $_=function($comment)
       {
           return "";
       };
        ob_start();
        eval('echo <<< EOT
        '.file_get_contents("themes/default/{$AUIEO_THEME_MODULE}.html").'
EOT;
');
        $html = ob_get_clean();
        Logger::getLogger("AuieoATS")->info("Template:loadTheme exit");
        return $html;
    }
}

?>
