dnl
dnl $ Id: $
dnl

PHP_ARG_ENABLE(hm_utils, whether to enable hm_utils functions,
[  --enable-hm_utils         Enable hm_utils support])

if test "$PHP_HM_UTILS" != "no"; then
  PHP_REQUIRE_CXX
  AC_LANG_CPLUSPLUS
  PHP_ADD_LIBRARY(stdc++,,HM_UTILS_SHARED_LIBADD)
  export OLD_CPPFLAGS="$CPPFLAGS"
  export CPPFLAGS="$CPPFLAGS $INCLUDES -DHAVE_HM_UTILS"

  AC_MSG_CHECKING(PHP version)
  AC_TRY_COMPILE([#include <php_version.h>], [
#if PHP_VERSION_ID < 40000
#error  this extension requires at least PHP version 4.0.0
#endif
],
[AC_MSG_RESULT(ok)],
[AC_MSG_ERROR([need at least PHP 4.0.0])])

  export CPPFLAGS="$OLD_CPPFLAGS"


  PHP_SUBST(HM_UTILS_SHARED_LIBADD)
  AC_DEFINE(HAVE_HM_UTILS, 1, [ ])

  PHP_NEW_EXTENSION(hm_utils, hm_utils.cpp , $ext_shared)

fi

