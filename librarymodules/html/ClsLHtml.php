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
include_once(dirname(__FILE__)."/ClsLHtmlReader.php");
include_once(dirname(__FILE__)."/ClsLHtmlGenerator.php");
include_once(dirname(__FILE__)."/ClsLHtmlSelect.php");
include_once(dirname(__FILE__)."/ClsLHtmlTable.php");
class ClsLHtml extends ClsNaanalLibrary 
{
    private static $reader=null;
    private static $writer=null;
    function __construct()
    {
    }
    public static function &getReader($html)
    {
        if(is_null(self::$reader))
        {
            self::$reader=new ClsLHtmlReader($html);
        }
        return self::$reader;
    }
    public static function &getGenerater()
    {
        if(is_null(self::$writer))
        {
            self::$writer=new ClsLHtmlGenerator();
        }
        return self::$writer;
    }
    public static function &getGenerator()
    {
        if(is_null(self::$writer))
        {
            self::$writer=new ClsLHtmlGenerator();
        }
        return self::$writer;
    }
}
?>