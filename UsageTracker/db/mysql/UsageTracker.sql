-- Database definition for UsageTracker
--
-- Part of BlueSpice MediaWiki
--
-- @author     Markus Glaser <glaser@hallowelt.com>

-- @package    BlueSpice_Extensions
-- @subpackage UsageTracker
-- @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
-- @filesource

CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_usagetracker(
	`ut_id`         int(5)           NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`ut_identifier` varchar(255)     NOT NULL default '',
	`ut_count`      int(10) unsigned NOT NULL default 0,
	`ut_type`       varchar(255)     NOT NULL default '',
	`ut_timestamp`  varchar(16)      NOT NULL default ''
)/*$wgDBTableOptions*/;