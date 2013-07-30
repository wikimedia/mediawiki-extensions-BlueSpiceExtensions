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

CREATE TABLE /*$wgDBprefix*/bs_searchstats (
	stats_id        serial          NOT NULL UNIQUE,
	stats_term      VARCHAR(255),
	stats_ts        VARCHAR(50),
	stats_user      INT,
	stats_hits      INT             DEFAULT NULL,
	stats_scope     VARCHAR(10)     DEFAULT NULL
);

DROP INDEX IF EXISTS /*$wgDBprefix*/idx_bs_searchstats_ttu;
CREATE INDEX /*$wgDBprefix*/idx_bs_searchstats_ttu ON /*$wgDBprefix*/bs_searchstats (stats_term, stats_ts, stats_user);


