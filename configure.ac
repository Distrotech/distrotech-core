#                                               -*- Autoconf -*-
# Process this file with autoconf to produce a configure script.

AC_PREREQ([2.69])
AC_INIT([distrotech-core], [14.9], [gregnietsky@gmail.com])

# Checks for programs.
AC_PROG_INSTALL
AC_PROG_LN_S

# Checks for libraries.
AM_INIT_AUTOMAKE

AC_ARG_WITH([dist-id], AS_HELP_STRING([--with-dist-id], [Distribution id stored in lsb-release (Distrotech)]))
AS_IF([test "x$with_dist_id" = "x" ], [DIST_ID=Distrotech], [DIST_ID=$with_dist_id])
AC_SUBST(DIST_ID)

AC_ARG_WITH([dist-release], AS_HELP_STRING([--with-dist-release], [Distribution release stored in lsb-release]))
AS_IF([test "x$with_dist_release" = "x" ], [DIST_RE=""], [DIST_RE=$with_dist_release])
AC_SUBST(DIST_RE)

AC_ARG_WITH([dist-codename], AS_HELP_STRING([--with-dist-codename], [Distribution codename stored in lsb-release]))
AS_IF([test "x$with_dist_codename" = "x" ], [DIST_CN=""], [DIST_CN=$with_dist_codename])
AC_SUBST(DIST_CN)

AC_ARG_WITH([dist-description], AS_HELP_STRING([--with-dist-description], [Distribution codename stored in lsb-release (Distrotech Solutions)]))
AS_IF([test "x$with_dist_description" = "x" ], [DIST_DI="Distrotech Solutions Linux"], [DIST_DI=$with_dist_description])
AC_SUBST(DIST_DI)

# Checks for header files.

# Checks for typedefs, structures, and compiler characteristics.

# Checks for library functions.

AC_CONFIG_FILES([Makefile
	 lsb/lsb-release])
AC_OUTPUT
