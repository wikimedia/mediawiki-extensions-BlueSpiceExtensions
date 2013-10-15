-- Database definition for statistics of ExtendedSearch
--
-- Part of BlueSpice for MediaWiki
--
-- @author     Thomas Lorenz <lorenz@hallowelt.biz>

-- @package    BlueSpice_Extensions
-- @subpackage ExtendedSearch
-- @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
-- @filesource

-- Dies ist eine Alternatie zu:
--   DROP INDEX IF EXISTS /*$wgDBprefix*/idx_bs_searchstats_ttu;
--   CREATE INDEX /*$wgDBprefix*/idx_bs_searchstats_ttu ON /*$wgDBprefix*/bs_searchstats (stats_term, stats_ts, stats_user);
-- Auf diese Weise muss der Index nicht gelöscht und neu erzeugt werden.
-- (s. ExtendedSearch.pg.sql)

create or replace function check_index_exists() returns text as $$
declare v_exists integer;
begin
select into v_exists count(*) from pg_class where relname = /*$wgDBprefix*/'idx_bs_searchstats_ttu';
if v_exists = 0 then
CREATE INDEX /*$wgDBprefix*/idx_bs_searchstats_ttu ON /*$wgDBprefix*/bs_searchstats (stats_term, stats_ts, stats_user);
return /*$wgDBprefix*/'index idx_bs_searchstats_ttu created on /*$wgDBprefix*/bs_searchstats';
else
return /*$wgDBprefix*/'index idx_bs_searchstats_ttu already exists';
end if;
end;
$$ language 'plpgsql';
select check_index_exists();
drop function check_index_exists();
