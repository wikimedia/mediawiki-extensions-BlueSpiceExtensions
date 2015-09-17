-- Database definition for statistics of ExtendedSearch
--
-- Part of BlueSpice for MediaWiki
--
-- @author     Mathias Scheer <scheer@hallowelt.biz>
-- @author     Stephan Muggli <muggli@hallowelt.biz>
-- @package    BlueSpice_Extensions
-- @subpackage ExtendedSearch
-- @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
-- @filesource

CREATE TABLE /*_*/bs_searchstats (
	`stats_id`    INT(10)      UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`stats_term`  VARCHAR(255)          NULL DEFAULT NULL,
	`stats_ts`    VARCHAR(50)           NULL DEFAULT NULL,
	`stats_user`  INT(10)      UNSIGNED NULL DEFAULT NULL,
	`stats_hits`  INT(10)      UNSIGNED NULL DEFAULT NULL,
	`stats_scope` VARCHAR(10)           NULL DEFAULT NULL
) /*$wgDBTableOptions*/;

CREATE	UNIQUE INDEX /*i*/stats_id  ON /*_*/bs_searchstats (stats_id);
CREATE	INDEX        /*i*/stats_term ON /*_*/bs_searchstats (stats_term);
CREATE	INDEX        /*i*/stats_ts   ON /*_*/bs_searchstats (stats_ts);
CREATE	INDEX        /*i*/stats_user ON /*_*/bs_searchstats (stats_user);
