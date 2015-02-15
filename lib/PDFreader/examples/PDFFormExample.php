<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>PDF Reader Form Example</title>
</head>
<body>
<?php
/**
 * PDFFormExample extracts key/value pairs from a PDF form and prints them out.
 * 
 * This is an example of PDF Reader's form extraction routine. It extracts
 * key/value pairs from a server side PDF file (uploading the file and providing 
 * the file path to PDF reader is your responsibility), and returns them as an
 * associative array. They key is the field name as defined in the PDF, and the
 * value is the data a user input into the field, if any. It then prints the key
 * in bold and the value in normal text, one row per field.
 * 
 * PHP version 5
 * 
 * @category  File_Formats
 * @package   PDF_Reader
 * @author    John M. Stokes <jstokes@heartofthefyre.us>
 * @copyright 2010 John M. Stokes
 * @license   http://www.opensource.org/licenses/bsd-license.html BSD Style License
 * @link      http://heartofthefyre.us/PDFreader/index.php
 */

require_once '../PDFreader/PDFreader.class.php';

$PDF = new PDFreader();
try {
    $PDF->open('/path/to/PDF/File/example.pdf');
    $formValues = $PDF->readForm();
}
catch(PDFexception $e) {
    echo '<p style="color: #FF0000; font-weight: bold; text-align: center;">';
    echo "$e</p>\n";
}

echo "<h2>Form fields</h2>
<p>\n";
foreach ($formValues as $key=>$value) {
    echo "<strong>$key:</strong> $value<br />\n";
}
echo "</p>\n";
?>
</body>
</html>