ALTER TABLE status ADD closed boolean default 'f';
ALTER TABLE contact ADD closed boolean default 'f';
ALTER TABLE list ADD dialretry integer default 1800;
