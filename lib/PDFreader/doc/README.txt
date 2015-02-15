******** ABOUT PDF READER ********
PDF Reader is a PHP 5 class tree that extracts text from a PDF file and returns it as an 
array of strings. It also supports AcroForms (aka FDF). It returns raw text as an array 
of strings or form fields as an associative array of key/value pairs.

PDF Reader is still in early development, so use at your own risk!

PDF reader will not run under PHP 4, as it uses PHP 5 class structure 
and Exceptions. Some portions based on PDFhi by Chung Leong 
(chernyshevsky@hotmail.com) and Zend Framework's PDF support, as noted
in the relevant method comments.     

I have no plans to extract images or layout metadata at this time, nor do I plan to support 
signed or encrypted PDFs unless there's demand.

SUPPORTED PDF VERSION: v1.7 and below
DECODERS: ASCII Hex, ASCII 85, LZW, and Flate Decoding. Image decoders not supported.
PREDICTORS: PNG Up unprediction only
CHARACTER SET: Unicode v5.2 Basic Latin and Latin-1 Supplement - i.e. character codes below \xFF (256)
ACROFORMS (FDF): supported
SIGNED PDFS: not supported
ENCRYPTION: not supported

@category  File_Formats
@package   File_PDFreader
@author    John M. Stokes <jstokes@heartofthefyre.us>
@copyright 2010 John M. Stokes
@license   http://www.opensource.org/licenses/bsd-license.html BSD Style License
@link      http://heartofthefyre.us/PDFreader/index.php


******** API ********
new PDFreader(); //Constructor
PDFreader::open(/path/to/PDF/file.pdf); //PDF File open method
PDFreader::readText(); //Raw text extraction method
PDFreader::readForm(); //Form field extraction method (For FDF/AcroForms)
PDFreader::close();  //(optional) PDF File close method
