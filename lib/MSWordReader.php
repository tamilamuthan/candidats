<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

class MSWordReader
{
    private $signature;
    private $subheader;
    private $docType="";
    private $encode="";
    private $fileInfo=array();
    private $streamInfo=array();
    public function __construct($filename) 
    {
        $handle=fopen($filename, "rb");
        $t=fread($handle, 2);
        $this->streamInfo["FibBase"]["wIdent"]=bin2hex($t);
        $t=fread($handle, 2);
        $this->streamInfo["FibBase"]["nFib"]=bin2hex($t);
        $t=fread($handle, 2);
        $this->streamInfo["FibBase"]["unused"]=bin2hex($t);
        $t=fread($handle, 2);
        $this->streamInfo["FibBase"]["lid"]=bin2hex($t);
        $t=fread($handle, 2);
        $this->streamInfo["FibBase"]["pnNext"]=bin2hex($t);
        $t=fread($handle, 2);
        $this->streamInfo["FibBase"]["A_to_M"]=bin2hex($t);
        $t=fread($handle, 2);
        $this->streamInfo["FibBase"]["nFibBack"]=bin2hex($t);
        $t=fread($handle, 4);
        $this->streamInfo["FibBase"]["lKey"]=bin2hex($t);
        $t=fread($handle, 1);
        $this->streamInfo["FibBase"]["envr"]=bin2hex($t);
        $t=fread($handle, 1);
        $this->streamInfo["FibBase"]["N_to_S"]=bin2hex($t);
        $t=fread($handle, 12);
       $this->streamInfo["FibBase"]["reserved"]=bin2hex($t);
      
       $t=fread($handle, 2);
       $this->streamInfo["csw"]=bin2hex($t);
       $t=fread($handle, 26);
       $this->streamInfo["fibRgW"]["reserved"]=bin2hex($t);
       $t=fread($handle, 2);
       $this->streamInfo["fibRgW"]["lidFE"]=bin2hex($t);
       $t=fread($handle, 2);
       $this->streamInfo["cslw"]=bin2hex($t);
       
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["cbMac"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["reserved1"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["reserved2"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["ccpText"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["ccpFtn"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["ccpHdd"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["reserved3"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["ccpAtn"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["ccpEdn"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["ccpTxbx"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["ccpHdrTxbx"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["reserved4"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["reserved5"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["reserved6"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["reserved7"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["reserved8"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["reserved9"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["reserved10"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["reserved11"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["reserved12"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["reserved13"]=bin2hex($t);
       $t=fread($handle, 4);
       $this->streamInfo["fibRgLw"]["reserved14"]=bin2hex($t);
       
       $t=fread($handle, 2);
       $this->streamInfo["cbRgFcLcb"]=bin2hex($t);
       $this->streamInfo["fibRgFcLcbBlob"]=bin2hex($t);
       
       $t=fread($handle, 2);
       $this->streamInfo["cswNew"]=bin2hex($t);
       $this->streamInfo["fibRgCswNew"]=bin2hex($t);       
       
        $fifo=  new finfo();
        $fileinfo=$fifo->file($filename);
        $arrFinfo=explode(",",$fileinfo);
        foreach($arrFinfo as $info)
        {
            $arr=explode(":",$info,2);
            if(isset($arr[1]))
            {
                $this->fileInfo[trim($arr[0])]=trim($arr[1]);
            }
            else
            {
                $this->fileInfo[]=trim($arr[0]);
            }
        }

        $filecontent=file_get_contents($filename);$arr=mb_decode_mimeheader($filecontent);//trace($this->fileInfo);
        $this->encode =  mb_detect_encoding($filecontent);
        
        $handle=fopen($filename, "r");
        fread($handle,0x0820);
        $msdoc="";
        while($chr=fread($handle,1))
        {
            if(ord($chr)==0x2D) 
            {
                break;
            }
            $msdoc=$msdoc.$chr;
        }
        fclose($handle);
        $this->docType=$msdoc;
    }
    public function isMSWordOld()
    {
        if($this->docType=="Microsoft Word") return false;
        else return true;
    }
}