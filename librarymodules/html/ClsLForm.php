<?php
/**
 * 
 *  require('ClsLForm.php');
 *  $frm = new ClsLForm(); // pass false for html rather than xhtml syntax
 *  // startForm arguments:
    // action, method (default is post), id,
    // optional associative array of additional attributes
    $str = $frm->startForm('result.php');
    // [form elements get added here]
    // end form when done
    $str .=  $frm->endForm();
 * 
 *  $str = $frm->startForm('result.php', 'post', 'myForm',
    // add optional attributes in associative array
    array('class'=>'demoForm', 'onsubmit'=>'return checkBeforeSubmit(this)') );
 *  // arguments: type, name, value
    $str .= $frm->addInput('text', 'firstName', 'Sharon');
 *  $str .= $frm->addInput('text', 'firstName', 'Sharon', 
    array('id'=>'firstName', 'size'=>16, 'maxlength'=>50) );
 *  // checkbox
    echo $frm->addInput('checkbox', 'brownies', 'likes');

    // radio
    echo $frm->addInput('radio', 'gender', 'male');
    echo $frm->addInput('radio', 'gender', 'female');

    // submit
    echo $frm->addInput('submit', 'submit', 'Submit');
 *  // for (id of associated form element), text
    echo $frm->addLabelFor('firstName', 'First Name: ');
 *  // arguments: name, rows, cols, value, optional assoc. array 
    echo $frm->addTextArea('comments', 4, 40, '',
    array('id'=>'comments', 'placeholder'=>'[your comments]') );
 *  $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 
        'August', 'September', 'October', 'November', 'December');

    
    //  addSelectListArrays arguments:
    //   name, array containing option values, array containing option text,
    //  optional: selected option's value, header, additional attributes in associative array
    //
        echo $frm->addSelectListArrays('month', range(1, 12), $months, '', ' - Month - ');
 *      $rank_ar = array('Totally lame', 'Minimally useful', 
        'Pretty good', 'I realy like it', 'Fabulous');

    //  addSelectList arguments: 
    //   name, array containing option text/values
    //   include values attributes (boolean),
    //   optional arguments: selected value, header,
    //   additional attributes in associative array
  
    echo $frm->addSelectList('rank', $rank_ar, false, 'Fabulous');
 * 
 *  // wrap form elements in paragraphs 
    $str .= $frm->startTag('p');
    // add form element(s) here
    // end tag
    $str .= $frm->endTag();
 */
class ClsLForm {
    
    private $tag;
    private $xhtml;
    
    function __construct($xhtml = true) {
        $this->xhtml = $xhtml;
    }
    
    function startForm($action = '#', $method = 'post', $id = '', $attr_ar = array() ) {
        $str = "<form action=\"$action\" method=\"$method\"";
        if ( !empty($id) ) {
            $str .= " id=\"$id\"";
        }
        $str .= $attr_ar? $this->addAttributes( $attr_ar ) . '>': '>';
        return $str;
    }
    
    private function addAttributes( $attr_ar ) {
        $str = '';
        // check minimized (boolean) attributes
        $min_atts = array('checked', 'disabled', 'readonly', 'multiple',
                'required', 'autofocus', 'novalidate', 'formnovalidate'); // html5
        foreach( $attr_ar as $key=>$val ) {
            if ( in_array($key, $min_atts) ) {
                if ( !empty($val) ) { 
                    $str .= $this->xhtml? " $key=\"$key\"": " $key";
                }
            } else {
                $str .= " $key=\"$val\"";
            }
        }
        return $str;
    }
    
    function addInput($type, $name, $value, $attr_ar = array() ) {
        $str = "<input type=\"$type\" name=\"$name\" value=\"$value\"";
        if ($attr_ar) {
            $str .= $this->addAttributes( $attr_ar );
        }
        $str .= $this->xhtml? ' />': '>';
        return $str;
    }
    
    function addTextarea($name, $rows = 4, $cols = 30, $value = '', $attr_ar = array() ) {
        $str = "<textarea name=\"$name\" rows=\"$rows\" cols=\"$cols\"";
        if ($attr_ar) {
            $str .= $this->addAttributes( $attr_ar );
        }
        $str .= ">$value</textarea>";
        return $str;
    }
    
    // for attribute refers to id of associated form element
    function addLabelFor($forID, $text, $attr_ar = array() ) {
        $str = "<label for=\"$forID\"";
        if ($attr_ar) {
            $str .= $this->addAttributes( $attr_ar );
        }
        $str .= ">$text</label>";
        return $str;
    }
    
    // from parallel arrays for option values and text
    function addSelectListArrays($name, $val_list, $txt_list, $selected_value = NULL,
            $header = NULL, $attr_ar = array() ) {
        $option_list = array_combine( $val_list, $txt_list );
        $str = $this->addSelectList($name, $option_list, true, $selected_value, $header, $attr_ar );
        return $str;
    }
    
    // option values and text come from one array (can be assoc)
    // $bVal false if text serves as value (no value attr)
    function addSelectList($name, $option_list, $bVal = true, $selected_value = NULL,
            $header = NULL, $attr_ar = array() ) {
        $str = "<select name=\"$name\"";
        if ($attr_ar) {
            $str .= $this->addAttributes( $attr_ar );
        }
        $str .= ">\n";
        if ( isset($header) ) {
            $str .= "  <option value=\"\">$header</option>\n";
        }
        foreach ( $option_list as $val => $text ) {
            $str .= $bVal? "  <option value=\"$val\"": "  <option";
            if ( isset($selected_value) && ( $selected_value === $val || $selected_value === $text) ) {
                $str .= $this->xhtml? ' selected="selected"': ' selected';
            }
            $str .= ">$text</option>\n";
        }
        $str .= "</select>";
        return $str;
    }
    
    function endForm() {
        return "</form>";
    }
    
    function startTag($tag, $attr_ar = array() ) {
        $this->tag = $tag;
        $str = "<$tag";
        if ($attr_ar) {
            $str .= $this->addAttributes( $attr_ar );
        }
        $str .= '>';
        return $str;
    }
    
    function endTag($tag = '') {
        $str = $tag? "</$tag>": "</$this->tag>";
        $this->tag = '';
        return $str;
    }
    
    function addEmptyTag($tag, $attr_ar = array() ) {
        $str = "<$tag";
        if ($attr_ar) {
            $str .= $this->addAttributes( $attr_ar );
        }
        $str .= $this->xhtml? ' />': '>';
        return $str;
    }
    
}

?>