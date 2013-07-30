-- Database definition for statistics of ExtendedSearch
--
-- Part of BlueSpice for MediaWiki
--
-- @author     Mathias Scheer <scheer@hallowelt.biz>
-- @package    BlueSpice_Extensions
-- @subpackage ExtendedSearch
-- @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
-- @filesource

CREATE SEQUENCE /*$wgDBprefix*/searchstats_id_seq MINVALUE 0 START WITH 0;
CREATE TABLE /*$wgDBprefix*/bs_searchstats (
	stats_id	NUMBER,
	stats_term	VARCHAR2(255),
	stats_ts	VARCHAR2(50),
	stats_user	NUMBER,
	stats_hits	NUMBER,
	stats_scope	VARCHAR2(10)
) /*$wgDBTableOptions*/;

ALTER TABLE /*$wgDBprefix*/bs_searchstats ADD CONSTRAINT /*$wgDBprefix*/bs_searchstats_pk PRIMARY KEY (stats_id);
CREATE INDEX stats_term_idx ON /*$wgDBprefix*/bs_searchstats (stats_term);
CREATE INDEX stats_ts_idx ON /*$wgDBprefix*/bs_searchstats (stats_ts);
CREATE INDEX stats_user_idx ON /*$wgDBprefix*/bs_searchstats (stats_user);

/*$mw$*/
CREATE OR REPLACE TRIGGER /*$wgDBprefix*/bs_stats_id_inc
BEFORE INSERT ON /*$wgDBprefix*/bs_searchstats
FOR EACH ROW
BEGIN
	SELECT /*$wgDBprefix*/searchstats_id_seq.nextval INTO :NEW.stats_id FROM dual;
END;
/*$mw$*/