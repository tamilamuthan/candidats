<?php
/**************************************************************************
 * PhpTrace 1.0, Simple, Efficient and Developer Friendly
 * Copyright (C) <2010>  <Tamil Amuthan. R>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ************************************************************************/

///Configuration Start
global $arrIgnore,$errIgnore, $errIgnoreCode, $errIgnorePattern, $ignoreErrorCount, $ignoreErrorFile, $ignoreErrorFolder, $errorFolder, $errorFile, $ignoreDebug;
$errIgnore = array();
$errIgnoreCode = array();
$errIgnorePattern = array();
$ignoreErrorCount = -1;
$ignoreErrorFile = array();
$ignoreErrorFolder = array();
$errorFolder = array();
$errorTraceFolder = array();
$errorFile = array();
$ignoreDebug = array();
///Configuration End
$arrIgnore=array("PHPTRACE_IGNORE_CODE","PHPTRACE_IGNORE_PATTERN","PHPTRACE_IGNORE_FILE","PHPTRACE_IGNORE_DIRECTORY","PHPTRACE_IGNORE_FILE","PHPTRACE_IGNORE_DIRECTORY");
$arrIgnored=array();
if(isset($error_handler))
{
    $e_hand=$error_handler[0];
    $$e_hand=PHPTrace::getInstance();
    $error_handler=array($$e_hand,$error_handler[1]);
}
foreach($arrIgnore as $PHPTRACE_IGNORE)
{
    if(isset($$PHPTRACE_IGNORE))
    {
        $$e_hand->setIgnored($PHPTRACE_IGNORE, $$PHPTRACE_IGNORE);
    }
}
if (file_exists(dirname(__FILE__) . "/trace.html")) {
    file_put_contents(dirname(__FILE__) . "/trace.html", "");
}

/*$getUserIP=function() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else
        $ip = $_SERVER['REMOTE_ADDR'];
    return $ip;
};*/

function errorCount($errCode = false, $isReturn = true) {
    static $__arrErrorCode = array();
    if ($isReturn) {
        if ($errCode === false) {
            $totcount = 0;
            if ($__arrErrorCode) {
                foreach ($__arrErrorCode as $code => $count) {
                    $totcount = $totcount + $count;
                }
                return $totcount;
            }
            else
                return 0;
        }
        else {
            return isset($__arrErrorCode[$errCode]) ? $__arrErrorCode[$errCode] : 0;
        }
    } else {
        $__arrErrorCode[$errCode] = isset($__arrErrorCode[$errCode]) ? $__arrErrorCode[$errCode] + 1 : 1;
    }
}

function errorList($error = false, $isReturn = true) {
    static $__arrErrorList = array();
    if ($isReturn) {
        $toterror = "";
        if ($__arrErrorList) {
            foreach ($__arrErrorList as $ind => $err) {
                $toterror = $toterror . "<br />" . $err;
            }
            return $toterror;
        }
        else
            return 0;
    }
    else {
        $__arrErrorList[] = $error;
    }
}

function showLog($message, $showTrace = false) {
    $width = "100%";
    $debug = "<table style='width:100%;'><tr><td style='vertical-align:top;width:{$width};'>";

    $debug = $debug . '---------------------TRACE&nbsp;START----------------------';
    $debug = $debug . '<br>';
    $debug = $debug . "<b>" . print_r($message, true) . "</b>";
    $debug = $debug . '<br>';
    $arr = debug_backtrace();
    $arrDetails = null;

    foreach ($arr as $ind => $value) {
        $debug = $debug . '<b>file:</b>' . (isset($value['file']) ? $value['file'] : null) . ' ';
        $debug = $debug . '<b>function call:</b>' . (isset($value['function']) ? $value['function'] : null) . ' <b>function:</b>' . (isset($arr[$ind + 1]['function']) ? $arr[$ind + 1]['function'] : null) . ' <b>line:</b>' . (isset($value['line']) ? $value['line'] : null) . '<br />';
        $arrDetails[$ind] = $value;
        unset($arrDetails[$ind]["file"]);
        unset($arrDetails[$ind]["line"]);
        unset($arrDetails[$ind]["function"]);
        if (!$showTrace)
            break;
    }
    $details = print_r($arrDetails, true);
    $details = str_replace("\n\n", "\n", $details);
    $details = str_replace("\n(", "(", $details);

    $debug = $debug . '----------------------TRACE&nbsp;END----------------------';
    $debug = $debug . '
	<br></td></tr></table>';
    echo $debug;
}

function traceToFile($message, $num = null, $isLog = false, $showTrace = true, $isAppend = false) {
    $fileName = dirname(__FILE__) . "/trace.php";
    ob_start();
    traceDirect($message, $num, $isLog, $showTrace, $fileName, $isAppend);
    $out=ob_get_clean();
    file_put_contents($fileName, $out, FILE_APPEND); 
}

function traceDirect($message, $num = null, $isLog = false, $showTrace = true, $fileName = false, $isAppend = false, $context = array()) {
    return trace($message, $num, $isLog, $showTrace, $fileName, $isAppend, $context,true);
}

function trace($message="", $num = null, $isLog = false, $showTrace = true, $fileName = false, $isAppend = false, $context = array(),$forceShowError=false,$arrIgnore=array()) {
    global $debugToFile, $errorTraceFolder;
    static $count = null;
    if($arrIgnore)
    {
        extract($arrIgnore);
    }
    if (isset($debugToFile) && $debugToFile === true) {
        $fileName = dirname(__FILE__) . "/trace.html";
        $isAppend = true;
    }

    $developerIP = defined("DEVELOPER_IP") ? DEVELOPER_IP : "";
    if(!isset($message)) $message="";
    $isDebugValid = false;
    if(file_exists(realpath(".")."/trace.ini"))
    {
        $arrINIData=parse_ini_file(realpath(".")."/trace.ini");
        if(isset($arrINIData["trace"]))
        {
            if($traceNum>0)
            {
                $num=$traceNum;
            }
        }
        $isDebugValid = true;
    }
    else if($forceShowError)
    {
        $isDebugValid = true;
    }
    else if (isset($_REQUEST["trace"])) {
        $traceNum=trim($_REQUEST["trace"]);
        if(is_numeric($traceNum) && $traceNum>0) $num=$traceNum;
        $isDebugValid = true;
    } else if (defined("PHPTRACE_DISPLAY_ERROR") && PHPTRACE_DISPLAY_ERROR!==false) {
        $isDebugValid = true;
        if(PHPTRACE_DISPLAY_ERROR!==true) $num=PHPTRACE_DISPLAY_ERROR;
    }
    else if(empty($developerIP))
    {
        $isDebugValid = true;
    }
    else if (!empty($developerIP)) {
        if ($developerIP == getUserIP()) {
            $isDebugValid = true;
        }
    }
    if (!$isDebugValid)
    {
        return;
    }
    if (is_null($num))
        $count = null;
    if (is_null($count)) {
        if (!is_null($num)) {
            $count = $num;
        }
    }

    $width = "40%";
    if ($isLog) {
        $width = "100%";
    }
    if(!file_exists("tmp/phptrace")) mkdir("tmp/phptrace", 0777, true);
    $debug = '<script type="text/javascript" src="js/jquery/jquery.js"></script>
            <link href="js/jquery/jqueryui.css" rel="stylesheet" type="text/css"/>;
  <script src="js/jquery/jqueryui.js"></script><script>
  $(document).ready(function() {
    $("#accordion").accordion({ heightStyle: "content" });
  });
  </script>';
    $debug = $debug . "<table style='width:100%;'><tr><td style='vertical-align:top;width:{$width};'>";

    //$debug=$debug.'---------------------TRACE&nbsp;START----------------------';
    $debug = $debug . '<br>';
    $debug = $debug . "<b>Total Errors:" . errorCount() . "</b><br />";
    $debug = $debug . "<b>" . print_r($message, true) . "</b>";
    //$debug=$debug.'<br>';
    $arr = debug_backtrace();
    /* if($arr[0]["function"]=="trace")
      {
      array_shift($arr);
      }
      if($arr[0]["function"]=="error_handler")
      {
      array_shift($arr);
      } */
    $debug = $debug . '<div id="accordion">';

    $arrDetails = null;
    $modaljs = '<script>function showDialog(v)
                    {
                        $( "#" + v ).dialog({
                        width:1200,
                            height: 600,
                            modal: true,
                            position: "top"
                        });
                    }</script>';
    $dialog = "";
    $tabcount=0;
    foreach ($arr as $ind => $value) 
    {
        if($errorTraceFolder)
        {
            $isContinue=true;
            foreach($errorTraceFolder as $ef)
            {
                if(strpos($value['file'], $ef)!==false)
                {
                    $isContinue=false;
                    break;
                }
            }
            if($isContinue) continue;
        }
        $tabcount=$tabcount+1;
        $debug = $debug . '<h3><a href="#" style="font-size:11px;">' . $tabcount . ') <b>file:</b>' . (isset($value['file']) ? $value['file'] : null) . ' ';
        $debug = $debug . (isset($value['class']) ? '<b>class:</b>' . $value['class'] . ' ' : "");
        $debug = $debug . (isset($value['type']) ? '<b>type:</b>' . $value['type'] . ' ' : "");
        $arg = isset($value["args"]) ? $value["args"] : "";

        ///To Do
        // $arrVar=get_defined_constants();
        $details = "";


        if ($context && isset($value['function']) && $value['function'] == "trace") {
            if(isset($_REQUEST) && !empty($_REQUEST)) ksort($_REQUEST);
            $context["_REQUEST"] = $_REQUEST;
            if(isset($_SESSION) && !empty($_SESSION))ksort($_SESSION);
            krsort($context);
            foreach ($context as $variable => $vvalue) {
                $details = '<a href="#" onclick="showDialog(\'' . $variable . '\')">' . $variable . "</a>, " . $details;

                $dialog = $dialog . '<div id="' . $variable . '" title="Data" style="overflow:scroll;width:100%;">
            <p><pre>' . print_r($vvalue, true) . '</pre></p>
    </div>';
            }
        }
        //$details="";
        ///
        if (isset($value['function']) && $value['function'] == "trace") {
            unset($arg[6]);
        } else if (isset($value['function']) && $value['function'] == "error_handler") {
            unset($arg[4]);
        }
        $debug = $debug . '<b>function:</b>' . (isset($value['function']) ? $value['function'] : null) . ' <b>Container&nbsp;Function:</b>' . (isset($arr[$ind + 1]['function']) ? $arr[$ind + 1]['function'] : null) . ' <b>line:</b>' . (isset($value['line']) ? $value['line'] : null) . '</a></h3>
                    <div>
		<p>
                <table style="width:1500px; padding:5px;"><tr><td style="width:1000px"><pre style="font-size:11px;">' . print_r($arg, true) . '</pre></td><td style="vertical-align:top;font-size:11px;">' . $details . '</td></tr></table>
                </p>
	</div>
                ';
        $arrDetails[$ind] = $value;
        unset($arrDetails[$ind]["file"]);
        unset($arrDetails[$ind]["line"]);
        unset($arrDetails[$ind]["function"]);
        unset($arrDetails[$ind]["class"]);
        unset($arrDetails[$ind]["type"]);
        unset($arrDetails[$ind]["object"]);
        if (!$showTrace) {
            break;
        }
    }
    $debug = $debug . '</div>';
    if (!$isLog) {
        $debug = $debug . $modaljs . $dialog . '</td>';
    }
    $debug = $debug . '
	<br></td></tr></table>';
    if (is_null($count)) {
        if ($fileName !== false) {
            if ($isAppend) {
                file_put_contents($fileName, $debug, FILE_APPEND);
            }
            else
                file_put_contents($fileName, $debug);
            return true;
        }
        else {
            echo $debug;
        }
        if (!$isLog) {
            exit;
        }
    } else if ($count === 1) {
        if ($fileName !== false) {
            if ($isAppend)
                file_put_contents($fileName, $debug, FILE_APPEND);
            else
                file_put_contents($fileName, $debug);
            return true;
        }
        else {
            echo $debug;
        }
        $count = null;
        if (!$isLog) {
            exit;
        }
    } else {
        $count--;
    }
}

function fatalErrorShutdownHandler() {
    if (function_exists("error_get_last")) {
        $last_error = error_get_last();
        if ($last_error['type'] === E_ERROR) {
            // fatal error
            error_handler(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
        }
    } else {
        error_handler(E_ERROR, "Fatel Error", "", "");
    }
}

function exception_handler(Exception $e) {
    $code = $e->getCode();
    $file = $e->getFile();
    $line = $e->getLine();
    $message = $e->getMessage();
    error_handler($code, $message, $file, $line);
}

function getErrorType($errno) {
    switch ($errno) {
        case E_USER_ERROR: $type = "E_USER_ERROR";
            break;
        case E_USER_WARNING: $type =
                    "E_USER_WARNING";
            break;
        case E_USER_NOTICE: $type = "E_USER_NOTICE";
            break;
        case E_ERROR: $type = "E_ERROR";
            break;
        case E_WARNING: $type = "E_WARNING";
            break;
        case E_PARSE: $type = "E_PARSE";
            break;
        case E_NOTICE: $type = "E_NOTICE";
            break;
        case E_CORE_ERROR: $type = "E_CORE_ERROR";
            break;
        case E_CORE_WARNING: $type =
                    "E_CORE_WARNING";
            break;
        case E_COMPILE_ERROR: $type =
                    "E_COMPILE_ERROR";
            break;
        case E_STRICT: $type = "E_STRICT";
            break;
        case E_COMPILE_WARNING: $type =
                    "E_COMPILE_WARNING";
            break;
        case E_RECOVERABLE_ERROR: $type =
                    "E_RECOVERABLE_ERROR";
            break;
        default: $type = "<br>This is an error not listed";
    }
    return $type;
}

function error_handler($code, $message, $file, $line, $context = false) {
    $isError = true;
    errorCount($code, false);
    $errortype = getErrorType($code);
    errorList($errortype . "(" . $code . ") at {$file} : $line - " . $message, false);
    global $errIgnore, $errIgnoreCode, $errIgnorePattern, $ignoreErrorCount, $ignoreErrorFile, $ignoreErrorFolder, $errorFolder, $errorFile, $ignoreDebug;
    if ($ignoreDebug)
        return true;
$errIgnoreCode=array(2);
if(function_exists("phptrace_ignore_code"))
{
    $arrNewIgnoreCode=phptrace_ignore_code();
    if($arrNewIgnoreCode)
    foreach($arrNewIgnoreCode as $code)
    {
        $errIgnoreCode[]=$code;
    }
}
    if (isset($errorFile) && !empty($errorFile) || isset($errorFolde) && !empty($errorFolder)) {
        $isError = false;
        if (isset($errorFile) && in_array($file, $errorFile)) {
            $isError = true;
        }
        if (!$isError) {
            if (!empty($errorFolder)) {
                foreach ($errorFolder as $f) {
                    if (strpos($file, $f) === 0) {
                        $isError = true;
                        break;
                    }
                }
            }
        }
    } else {
        if (isset($ignoreErrorFile) && in_array($file, $ignoreErrorFile)) {
            $isError = false;
        } else if (isset($ignoreErrorFolder)) {
            foreach ($ignoreErrorFolder as $f) {
                if (strpos($file, $f) === 0) {
                    $isError = false;
                    break;
                }
            }
        }
    }
    if (empty($errIgnore)) {
        $errIgnore = array();
    }
    if (empty($errIgnoreCode)) {
        $errIgnoreCode = array();
    }
    if (empty($errIgnorePattern)) {
        $errIgnorePattern = array();
    }
    //$arrIgnore=null;
    //$arrIgnore[]="E_STRICT";
    ///skipping the error of ignore specified in config.xml
    if (in_array($errortype, $errIgnore) || in_array($code, $errIgnoreCode)) {
        $isError = false;
    }
    foreach ($errIgnorePattern as $pattern) {
        if (strpos($message, $pattern) !== false) {
            $isError = false;
            break;
        }
    }
    if (!$isError)
        return true;

    if (isset($ignoreErrorCount) && $ignoreErrorCount > 0 && errorCount($code) < $ignoreErrorCount) {
        trace($code . ": " . $message . "<br /> File:" . $file . ", Line:" . $line, $ignoreErrorCount + 1, false, true, false, false, $context);
    } else {
        trace($code . ": " . $message . "<br /> File:" . $file . ", Line:" . $line, null, false, true, false, false, $context);
    }
}
if(defined("DEVELOPER_MODE") && DEVELOPER_MODE==true)
{
    if (isset($disableAutoTrace))
    {
    }
    else 
    {
        if(!isset($error_handler))
        {
            $error_handler="error_handler";
        }
        if($error_handler!==false) set_error_handler($error_handler);
        if(!isset($exception_handler))
        {
            $exception_handler="exception_handler";
        }
        if($exception_handler!==false) set_exception_handler($exception_handler);
        if(!isset($fatal_shutdown_handler))
        {
            $fatal_shutdown_handler="fatalErrorShutdownHandler";
        }
        register_shutdown_function($fatal_shutdown_handler);
    }
}
?>
