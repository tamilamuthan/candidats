/*
   +----------------------------------------------------------------------+
   | This library is free software; you can redistribute it and/or        |
   | modify it under the terms of the GNU Lesser General Public           |
   | License as published by the Free Software Foundation; either         |
   | version 2.1 of the License, or (at your option) any later version.   | 
   |                                                                      |
   | This library is distributed in the hope that it will be useful,      |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU    |
   | Lesser General Public License for more details.                      | 
   |                                                                      |
   | You should have received a copy of the GNU Lesser General Public     |
   | License in the file LICENSE along with this library;                 |
   | if not, write to the                                                 | 
   |                                                                      |
   |   Free Software Foundation, Inc.,                                    |
   |   51 Franklin Street, Fifth Floor,                                   |
   |   Boston, MA  02110-1301  USA                                        |
   +----------------------------------------------------------------------+
   | Authors: Jason Munro jason@hastymail.org                             |
   +----------------------------------------------------------------------+
*/

/* $ Id: $ */ 

#include "php_hm_utils.h"
#include "utils.h"

#if HAVE_HM_UTILS

function_entry hm_utils_functions[] = {
	PHP_FE(hm_html_strlen      , hm_html_strlen_arg_info)
	PHP_FE(hm_html_trim        , hm_html_trim_arg_info)
	PHP_FE(hm_html_entities    , hm_html_entities_arg_info)
	PHP_FE(hm_crypt            , hm_crypt_arg_info)
	PHP_FE(hm_decrypt          , hm_decrypt_arg_info)
	PHP_FE(hm_utf8_to_html     , hm_utf8_to_html_arg_info)
	PHP_FE(hm_parse_imap_line  , hm_parse_imap_line_arg_info)
	{ NULL, NULL, NULL }
};
zend_module_entry hm_utils_module_entry = {
	STANDARD_MODULE_HEADER, "hm_utils",
	hm_utils_functions, NULL, NULL,
	NULL, NULL, PHP_MINFO(hm_utils),
	"0.0.1", STANDARD_MODULE_PROPERTIES
};

HastymailUtils hm;

#ifdef COMPILE_DL_HM_UTILS
extern "C" { ZEND_GET_MODULE(hm_utils) }
#endif

PHP_MINFO_FUNCTION(hm_utils) {
	php_printf("<table width=\"600\" cellpadding=\"3\" border=\"0\"><tr><td class=\"e\">Hastymail Utilities</td><td class=\"v\">enabled</td></tr>\n");
	php_printf("<tr><td class=\"e\">Version</td><td class=\"v\">0.0.1</td></tr>\n");
    php_printf("<tr><td class=\"e\">Build Date</td><td class=\"v\">%s %s</td></tr>\n", __TIME__, __DATE__);
	php_printf("<tr><td class=\"e\">Author</td><td class=\"v\">Jason Munro &lt;jason@hastymail.org&gt;</td></tr></table>\n");
}
PHP_FUNCTION(hm_html_strlen) {
	const char * str = NULL;
	int str_len = 0;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &str, &str_len) == FAILURE) { return; }
	string strInput = str;
	RETURN_LONG(hm.hm_html_strlen(strInput));
}
PHP_FUNCTION(hm_html_trim) {
	const char * str = NULL;
	int str_len = 0;
	long len = 0;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sl", &str, &str_len, &len) == FAILURE) { return; }
	string strInput = str;
	RETURN_STRING(hm.hm_html_trim(strInput, len), 1);
}
PHP_FUNCTION(hm_html_entities) {
	const char * str = NULL;
	int str_len = 0;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &str, &str_len) == FAILURE) { return; }
	string strInput = str;
	RETURN_STRING(hm.hm_html_entities(strInput), 1);
}
PHP_FUNCTION(hm_crypt) {
	const char * str = NULL;
	int str_len = 0;
	const char * key = NULL;
	int key_len = 0;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ss", &str, &str_len, &key, &key_len) == FAILURE) { return; }
	string input = str;
	string ckey = key;
	RETURN_STRING(hm.hm_crypt(input, ckey), 1);
}
PHP_FUNCTION(hm_decrypt) {
	const char * str = NULL;
	int str_len = 0;
	const char * key = NULL;
	int key_len = 0;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "ss", &str, &str_len, &key, &key_len) == FAILURE) { return; }
	string input = str;
	string ckey = key;
	RETURN_STRING(hm.hm_decrypt(input, ckey), 1);
}
PHP_FUNCTION(hm_utf8_to_html) {
	const char * str = NULL;
	int str_len = 0;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &str, &str_len) == FAILURE) { return; }
	string input = str;
	RETURN_STRING(hm.hm_utf8_to_html(input), 1);
}
PHP_FUNCTION(hm_parse_imap_line) {
	const char * str = NULL;
	long str_len = 0;
	long curr_len;
	long max_len;
	zval* handle;
	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sllr", &str, &str_len, &curr_len, &max_len, &handle) == FAILURE) { return; }
	string input = str;
	hm.hm_parse_imap_line(input, curr_len, max_len, handle, return_value);
}
#endif
