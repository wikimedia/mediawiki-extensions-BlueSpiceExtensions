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

CREATE TABLE /*$wgDBprefix*/bs_shoutbox (
	sb_id			serial		NOT NULL 	PRIMARY KEY,
	sb_page_id		int			NOT NULL 	DEFAULT 0,		/* foreign key to page.page_id */
	sb_user_id		int			NOT NULL 	DEFAULT 0,		/* foreign key to user.user_id */
	sb_timestamp	varchar(16)	NOT NULL 	DEFAULT '',	/* timestamp YmdHis */
	sb_user_name	text		NOT NULL 	DEFAULT '',	/* foreign key to user.user_name */
	sb_message		text		NOT NULL 	DEFAULT '',
	sb_archived		BOOLEAN		NULL 		DEFAULT FALSE
);

CREATE INDEX /*i*/sb_page_id ON /*$wgDBprefix*/bs_shoutbox (sb_page_id);