<?php
function navigateFileSystem($directoryPath,$callBack)
{
    $fs=new FilesystemIterator($directoryPath);
    foreach($fs as $fileInfo)
    {
        if($fileInfo->isDir())
        {
            navigateFileSystem($fileInfo->getPath()."/".$fileInfo->getFilename(),$callBack);
        }
        else
        {
            $callBack($fileInfo);
        }
    }
}
function fCallBack($fileInfo)
{
    $objDatabase = DatabaseConnection::getInstance();
    $ext=$fileInfo->getExtension();
    if($ext!="ini") return;
    $realPath=$fileInfo->getRealPath();
    $table=$fileInfo->getBasename(".ini");
    if(!$objDatabase->isTableExist($table))
    {
        $objDatabase->createTable($table);
    }
    $arrIniField=parse_ini_file($realPath, true);
    foreach($arrIniField as $field=>$arrData)
    {
        if(!$objDatabase->isFieldExist($table,$field))
        {
            $type=isset($arrData["type"])?$arrData["type"]:"VARCHAR";
            if(strtolower($type)=="varchar")
            {
                $size=isset($arrData["size"])?$arrData["size"]:255;
            }
            else
            {
                $size=isset($arrData["size"])?$arrData["size"]:11;
            }
            $objDatabase->addField($table,$field,$type,$size);
        }
    }
}
navigateFileSystem(__DIR__."/extra","fCallBack");

?>