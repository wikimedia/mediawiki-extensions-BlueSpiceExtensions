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

CREATE TABLE /*$wgDBprefix*/bs_searchstats (
	`stats_id`    INT(10)      UNSIGNED NOT NULL AUTO_INCREMENT,
	`stats_term`  VARCHAR(255)          NULL DEFAULT NULL,
	`stats_ts`    VARCHAR(50)           NULL DEFAULT NULL,
	`stats_user`  INT(10)      UNSIGNED NULL DEFAULT NULL,
	`stats_hits`  INT(10)      UNSIGNED NULL DEFAULT NULL,
	`stats_scope` VARCHAR(10)           NULL DEFAULT NULL,
	PRIMARY KEY ( stats_id ),
	UNIQUE INDEX `stats_id`   (`stats_id`),
	INDEX        `stats_term` (`stats_term`),
	INDEX        `stats_ts`   (`stats_ts`),
	INDEX        `stats_user` (`stats_user`)
) /*$wgDBTableOptions*/;
