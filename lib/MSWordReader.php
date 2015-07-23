<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

class MSWordReader
{
    private $signature;
    private $subheader;
    private $docType="";
    public function __construct($filename) 
    {
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