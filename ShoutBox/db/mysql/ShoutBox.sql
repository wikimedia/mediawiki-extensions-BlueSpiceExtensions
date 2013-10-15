-- Database definition for Shoutbox
--
-- Part of BlueSpice for MediaWiki
--
-- @author     Markus Glaser <glaser@hallowelt.biz>

-- @package    BlueSpice_Extensions
-- @subpackage ShoutBox
-- @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
-- @filesource

CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_shoutbox(
	`sb_id`        int(5)           NOT NULL auto_increment,
	`sb_page_id`   int(10)          NOT NULL default 0,        /* foreign key to page.page_id */
	`sb_user_id`   int(10) unsigned NOT NULL default 0,        /* foreign key to user.user_id */
	`sb_timestamp` varchar(16)      NOT NULL default '',       /* timestamp YmdHis */
	`sb_user_name` varbinary(255)   NOT NULL default '',       /* foreign key to user.user_name */
	`sb_message`   blob             NOT NULL default '',
	`sb_archived`  BOOLEAN          NOT NULL default 0,
	`sb_title`     varbinary(255)   NOT NULL default '',
	`sb_touched`   varchar(14)      NOT NULL default '',
	`sb_parent_id` int(5) unsigned  NOT NULL default 0,
	PRIMARY KEY (`sb_id`)
)/*$wgDBTableOptions*/;

CREATE INDEX /*i*/sb_page_id ON /*$wgDBprefix*/bs_shoutbox (sb_page_id);
CREATE INDEX /*i*/sb_user_id ON /*$wgDBprefix*/bs_shoutbox (sb_user_id);
CREATE INDEX /*i*/sb_parent_id ON /*$wgDBprefix*/bs_shoutbox (sb_parent_id);