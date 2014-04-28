all:

install: all
	install -d $(DESTDIR)/
	rsync -a --exclude=Makefile --exclude=.git . $(DESTDIR)/
