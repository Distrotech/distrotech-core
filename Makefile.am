ACLOCAL_AMFLAGS = -I m4
EXTRA_DIST = sysroot m4
DIST_TARGETS = dist-gzip dist-bzip2 dist-xz dist-zip

install-data-local:
	install -d $(DESTDIR)/
	rsync -a --exclude=.git sysroot/* $(DESTDIR)/

distclean-local:
	rm -rf $(top_srcdir)/autom4te.cache $(top_srcdir)/config.h.in~ || true
