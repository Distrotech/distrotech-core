all:

install: all
	install -d $(DESTDIR)/
	rsync -Ra --exclude=.git */* $(DESTDIR)/
