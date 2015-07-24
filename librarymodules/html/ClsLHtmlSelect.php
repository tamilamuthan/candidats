<?php
/**************************************************************************
 * Naanal PHP Framework, Simple, Efficient and Developer Friendly
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

class ClsLHtmlselect
{
	private $name="";
	private $arrAssoc=array();
	private $param=array();
	private $selectedValue=array();
	public $emptyValueCaption="- Select -";
        public $emptyValue="";
        private $isMultiple=false;
	function __construct($name,$arrAssoc)
	{
		$this->name=$name;
		$this->arrAssoc=$arrAssoc;
	}
        /**
         * 
         * @param type $selectedValue - single value or array of value
         */
	function setSelected($selectedValue)
	{
            if(is_array($selectedValue))
            {
                foreach($selectedValue as $v)
                {
                    $this->selectedValue[]=$v;
                }
            }
            else
            {
		$this->selectedValue[]=$selectedValue;
            }
	}
	function setParam($key,$value)
	{
            if(strtolower($key)=="multiple" || strtolower($value)=="multiple")
                $this->isMultiple=true;
            $this->param[$key]=$value;
	}
	function render()
	{
		$param="";
		foreach($this->param as $k=>$v)
		{
			if($param=="")
			{
				$param=$k.'="'.$v.'"';
			}	
			else
			{
				$param=$param.' '.$k.'="'.$v.'"';
			}
		}
                $multiple=$this->isMultiple?"[]":"";
		$select='<select name="'.$this->name.$multiple.'" id="'.$this->name.'" '.$param.'>
		<option value="'.($this->emptyValue).'">'.$this->emptyValueCaption.'</option>';
		if($this->arrAssoc)
		foreach($this->arrAssoc as $k=>$v)
		{
                    $selected="";
                    if(in_array($k,$this->selectedValue)) $selected=" selected";
                    $select=$select.'<option value="'.$k.'"'.$selected.'>'.$v.'</option>';	
		}  		
		$select=$select.'</select>';
		return $select;
	}
}
?>