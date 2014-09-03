all:

install: all
	install -d $(DESTDIR)/
	rsync -a --exclude=.git sysroot/* $(DESTDIR)/
