<?php

/*
Author : Hemant Kr Tiwari
Email : t.hemantkumar@gmail.com
contact : +91-9818664766
licence : GNU General Public License
*/

class BooleanSearchQuery{


    public $boolean_string;
    /*
    * default cunstructor to this class
    */
    function __construct($data) {
        $this->boolean_string = "";
        $this->getStringForBooleanSearch($data);
    }

    
    /**
     * This function is used to return array in a string format.
    */

    function convertAsString($data = NULL) {
        $str = "";
        $str .= implode(" ", $data);
        return $str;
    }


    #############
    /**
    * This function is used to parse string and convert it in array.
    */

    function getStrArray(&$searchTxt){
        $str_len = strlen($searchTxt);
        $cflag = FALSE;
        $temp_str = "";
        $str_arr = array(); 
        $qoutes_flag = "";
        for($i=0; $i<$str_len; $i++) {
            if($searchTxt[$i] == '(' || $searchTxt[$i] == ')' || $searchTxt[$i] == '"') { 
                if(!empty($temp_str)) {
                    array_push($str_arr,  $temp_str);
                    $temp_str = "";
                }
                array_push($str_arr,  $searchTxt[$i]);        
                continue;
            }
            if($i==0) {
                $cflag = TRUE;
                $temp_str .= $searchTxt[$i];    
                $qoutes_flag = $searchTxt[$i];
            } else {
                if(!$cflag) {
                    $cflag = TRUE;
                    $temp_str .= $searchTxt[$i];
                    $qoutes_flag = $searchTxt[$i];    
                } else {
                    if($cflag && ( ($qoutes_flag == '"' && $searchTxt[$i] != '"') || ($qoutes_flag != '"' &&  $searchTxt[$i] != ' '))) {
                        $temp_str .= $searchTxt[$i];    
                    } else if($cflag  && ( $searchTxt[$i] == '"' || $searchTxt[$i] == ' ')){
                        $temp_str .= $searchTxt[$i];    
                        $cflag = FALSE;
                        $temp_str = trim($temp_str);
                        if(!empty($temp_str))
                            array_push($str_arr,  $temp_str);        
                        $temp_str = "";
                    }
                }
            }
        }
        $temp_str = trim($temp_str);
        if(!empty($temp_str))
            array_push($str_arr,  $temp_str);        
                        
        $str_arr = array_values(array_filter($str_arr));
        return $str_arr;
    }

    /**
    * This function is used to process array for Boolean operator.
    */
    
    function processArray(&$str_array) {
        $tempArr = array();
        $tempStr = "";
        $cflag = FALSE;
        foreach($str_array as $value) {
            $value = trim($value);
            if(empty($value))
                continue;
                
            if(!$cflag && $value == '"'){
                $tempStr .=  $value;
                $cflag = TRUE;
                continue;
            }
            if($cflag && $value != '"') {
                $tempStr .=  $value." ";
            }
            if($cflag && $value == '"') {
                $tempStr = trim($tempStr);
                $tempStr .=  $value;
                $cflag = FALSE;
            }
            if(!$cflag) {
                if(empty($tempStr))
                    $tempStr = $value;
                array_push($tempArr, $tempStr);
                $tempStr = "";
            }
        }
        $ndflag = 0;
        $finalArr  = array();
        $excludeArr = array("and", "or", "not");
        while(!empty($tempArr)) {
            $temp1 = array_pop($tempArr);
            $temp1 = trim($temp1);
        
            if(empty($temp1))
                continue;
                
            if(empty($finalArr)) {
                array_push($finalArr, $temp1);
            } else {
                $temp2 = array_pop($finalArr);
                $temp2 = trim($temp2);
                if((!in_array($temp2, $excludeArr) && $temp1 == '(') || (!in_array($temp1, $excludeArr) && $temp2 == '(')){
                    $this->push_data($finalArr, $temp2, $temp1);
                } else if(!in_array($temp2, $excludeArr) && $temp1 == ')'){
                    $this->push_data($finalArr, $temp2, $temp1, 'and');
                } else if(!in_array($temp1, $excludeArr) && $temp2 == ')'){
                    $this->push_data($finalArr, $temp2, $temp1);
                } else if(!empty($temp2) && !empty($temp1) && !in_array($temp2, $excludeArr) && !in_array($temp1, $excludeArr)){
                    $this->push_data($finalArr, $temp2, $temp1, 'and');
                } else {
                    $this->push_data($finalArr, $temp2, $temp1);
                }
            }
        }
        $finalArr = array_reverse($finalArr);
        return $finalArr;
    }

    /**
    * This function is used to push data in array.
    * */
    function push_data(&$finalArr, &$temp2, &$temp1, $operator = NULL){
        if($temp2 == 'not' && $temp1 == 'and'){
            array_push($finalArr, 'not');
        } else {
            array_push($finalArr, $temp2);
            if(!empty($operator))
                array_push($finalArr, $operator);
            array_push($finalArr, $temp1);
        }
    }

     /**
     * This function is used to set the operator.
     */

    function setOperator($temp_stack, &$operator, $temp) {
        $operator = ((count($temp_stack) > 0) ? $temp : "");
    }

    /**
     * This function is used to update the keyword according to boolean operator.
     */

    function updateStack(&$temp_stack, $operator) {
        $temp = array_pop($temp_stack);
        if($operator == 'and'){
            $temp = '+'.$temp;    
        } else if($operator == 'not'){
            $temp = '-'.$temp;
        }
        array_push($temp_stack,  $temp);    
    }

    /**
     * This function is used to generate boolean search string on the basis of AND OR  NOT operator.
    */
     
    function getStringForBooleanSearch($str = "") {
    
        $boolean_string = $operator = $temp = "";
        $str_array = $temp_stack = array();
        
        if(empty($str)) 
            return $boolean_string; 
        
        $str = strtolower($str);
        $str_array = $this->getStrArray($str);
        $str_array = $this->processArray($str_array);
        
        do{
            $temp = array_pop($str_array); 
            switch($temp){
                case 'and':
                        $this->setOperator($temp_stack, $operator, $temp);
                        break;
                case 'or':
                        $this->setOperator($temp_stack, $operator, $temp);
                        break;
                case 'not':
                        $this->setOperator($temp_stack, $operator, $temp);
                        break;
                default:
                    $this->updateStack($temp_stack, $operator);
                    array_push($temp_stack,  $temp);
            }
        }while(!empty($str_array));
                    
        if($operator == 'and')
            $this->updateStack($temp_stack, $operator);
        
        $temp_stack = array_reverse($temp_stack);
        $this->boolean_string = $this->convertAsString($temp_stack);
    }
}

?>