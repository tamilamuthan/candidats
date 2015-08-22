<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ModuleEmailTemplate
{
    private $baseModule=null;
    public function __construct($baseModule)
    {
        $this->baseModule=$baseModule;
    }
    public function __get($var)
    {
        $baseModule=$this->baseModule;
        return $this->$baseModule->$var;
    }
}
?>