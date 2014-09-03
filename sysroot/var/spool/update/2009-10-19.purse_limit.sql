CREATE TABLE purse_limit (
    date timestamp without time zone DEFAULT now(),
    ammount integer,
    name character varying(16)
);
ALTER TABLE public.purse_limit OWNER TO asterisk;
CREATE INDEX purse_limit_date ON purse_limit USING btree (date);
CREATE INDEX purse_limit_name ON purse_limit USING btree (name);
CREATE VIEW purse_update AS SELECT name,sum(ammount) AS ammount FROM purse_limit WHERE date > date_trunc('month',now()) GROUP BY name;
