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
    private function &loadTemplateVars($__AUIEO__TEMPLATE__FILE)
    {
        if(!file_exists($__AUIEO__TEMPLATE__FILE)) return array();
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
     * Evaluates a template file. All assignments (see the Template::assign()
     * and Template::assignByReference() methods) must be made before calling
     * this method. The template filename is relative to index.php.
     *
     * @param string template filename
     * @return void
     */
    public function display($template)
    {
        /* File existence checking. */
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
        
       /**
        * generate master html template from auieo.php and auieo.html
        */
        try
        {
            ///for late rendering from module template - replace the same variable
            $AUIEO_MODULE_CONTENT='{$AUIEO_MODULE_CONTENT}';
            ///for late rendering from module template - replace the same variable
            $AUIEO_HEADER='{$AUIEO_HEADER}';
            $arrTplVar=$this->loadTemplateVars("auieo/auieo.php");
            extract($arrTplVar);
            $_AUIEO_TEMPLATE_MASTER=file_get_contents("auieo/auieo.html");
            ob_start();
            $html="";
            eval('echo <<< EOT
    '.$_AUIEO_TEMPLATE_MASTER.'
EOT;
');
            $_AUIEO_TEMPLATE_MASTER = ob_get_clean();
        }
        catch(Exception $e)
        {
            trace($e);
        }
        

        /* Include the template, with output buffering on, and echo it. */
        $arrPathInfo=pathinfo($this->_templateFile);
        if($arrPathInfo["extension"]=="php" && (file_exists("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.html") || file_exists("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.htm")))
        {
           $arrTplVar=$this->loadTemplateVars($this->_templateFile);
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
                $AUIEO_MODULE_CONTENT = ob_get_clean();
                
                ob_start();
                $html="";
                eval('echo <<< EOT
        '.$_AUIEO_TEMPLATE_MASTER.'
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

        echo($html);
    }
}

?>
