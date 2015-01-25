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
   | Authors: Unknown User <unknown@example.com>                          |
   +----------------------------------------------------------------------+
*/

/* $ Id: $ */ 

#ifndef PHP_HM_UTILS_H
#define PHP_HM_UTILS_H

#ifdef  __cplusplus
extern "C" {
#endif

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include <php.h>

#ifdef HAVE_HM_UTILS

#include <php_ini.h>
#include <SAPI.h>
#include <ext/standard/info.h>
#include <Zend/zend_extensions.h>
#ifdef  __cplusplus
} // extern "C" 
#endif
#ifdef  __cplusplus
extern "C" {
#endif

extern zend_module_entry hm_utils_module_entry;
#define phpext_hm_utils_ptr &hm_utils_module_entry

#ifdef PHP_WIN32
#define PHP_HM_UTILS_API __declspec(dllexport)
#else
#define PHP_HM_UTILS_API
#endif

PHP_MINIT_FUNCTION(hm_utils);
PHP_MSHUTDOWN_FUNCTION(hm_utils);
PHP_RINIT_FUNCTION(hm_utils);
PHP_RSHUTDOWN_FUNCTION(hm_utils);
PHP_MINFO_FUNCTION(hm_utils);

#ifdef ZTS
#include "TSRM.h"
#endif

#define FREE_RESOURCE(resource) zend_list_delete(Z_LVAL_P(resource))

#define PROP_GET_LONG(name)    Z_LVAL_P(zend_read_property(_this_ce, _this_zval, #name, strlen(#name), 1 TSRMLS_CC))
#define PROP_SET_LONG(name, l) zend_update_property_long(_this_ce, _this_zval, #name, strlen(#name), l TSRMLS_CC)

#define PROP_GET_DOUBLE(name)    Z_DVAL_P(zend_read_property(_this_ce, _this_zval, #name, strlen(#name), 1 TSRMLS_CC))
#define PROP_SET_DOUBLE(name, d) zend_update_property_double(_this_ce, _this_zval, #name, strlen(#name), d TSRMLS_CC)

#define PROP_GET_STRING(name)    Z_STRVAL_P(zend_read_property(_this_ce, _this_zval, #name, strlen(#name), 1 TSRMLS_CC))
#define PROP_GET_STRLEN(name)    Z_STRLEN_P(zend_read_property(_this_ce, _this_zval, #name, strlen(#name), 1 TSRMLS_CC))
#define PROP_SET_STRING(name, s) zend_update_property_string(_this_ce, _this_zval, #name, strlen(#name), s TSRMLS_CC)
#define PROP_SET_STRINGL(name, s, l) zend_update_property_stringl(_this_ce, _this_zval, #name, strlen(#name), s, l TSRMLS_CC)


PHP_FUNCTION(hm_html_strlen);
#if (PHP_MAJOR_VERSION >= 5)
ZEND_BEGIN_ARG_INFO_EX(hm_html_strlen_arg_info, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
  ZEND_ARG_INFO(0, str)
ZEND_END_ARG_INFO()
#else /* PHP 4.x */
#define hm_html_strlen_arg_info NULL
#endif

PHP_FUNCTION(hm_html_trim);
#if (PHP_MAJOR_VERSION >= 5)
ZEND_BEGIN_ARG_INFO_EX(hm_html_trim_arg_info, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
  ZEND_ARG_INFO(0, str)
  ZEND_ARG_INFO(0, len)
ZEND_END_ARG_INFO()
#else /* PHP 4.x */
#define hm_html_trim_arg_info NULL
#endif

PHP_FUNCTION(hm_html_entities);
#if (PHP_MAJOR_VERSION >= 5)
ZEND_BEGIN_ARG_INFO_EX(hm_html_entities_arg_info, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
  ZEND_ARG_INFO(0, str)
ZEND_END_ARG_INFO()
#else /* PHP 4.x */
#define hm_html_entities_arg_info NULL
#endif

PHP_FUNCTION(hm_crypt);
#if (PHP_MAJOR_VERSION >= 5)
ZEND_BEGIN_ARG_INFO_EX(hm_crypt_arg_info, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
  ZEND_ARG_INFO(0, str)
  ZEND_ARG_INFO(0, key)
ZEND_END_ARG_INFO()
#else /* PHP 4.x */
#define hm_crypt_arg_info NULL
#endif

PHP_FUNCTION(hm_decrypt);
#if (PHP_MAJOR_VERSION >= 5)
ZEND_BEGIN_ARG_INFO_EX(hm_decrypt_arg_info, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 2)
  ZEND_ARG_INFO(0, str)
  ZEND_ARG_INFO(0, key)
ZEND_END_ARG_INFO()
#else /* PHP 4.x */
#define hm_decrypt_arg_info NULL
#endif

PHP_FUNCTION(hm_utf8_to_html);
#if (PHP_MAJOR_VERSION >= 5)
ZEND_BEGIN_ARG_INFO_EX(hm_utf8_to_html_arg_info, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
  ZEND_ARG_INFO(0, str)
ZEND_END_ARG_INFO()
#else /* PHP 4.x */
#define hm_utf8_to_html_arg_info NULL
#endif

PHP_FUNCTION(hm_parse_imap_line);
#if (PHP_MAJOR_VERSION >= 5)
ZEND_BEGIN_ARG_INFO_EX(hm_parse_imap_line_arg_info, ZEND_SEND_BY_VAL, ZEND_RETURN_VALUE, 1)
  ZEND_ARG_INFO(0, str)
ZEND_END_ARG_INFO()
#else /* PHP 4.x */
#define hm_parse_imap_line NULL
#endif

#ifdef  __cplusplus
} // extern "C" 
#endif
#endif /* PHP_HAVE_HM_UTILS */
#endif /* PHP_HM_UTILS_H */
