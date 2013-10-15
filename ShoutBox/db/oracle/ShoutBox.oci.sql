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

--CREATE SEQUENCE /*$wgDBprefix*/shoutbox_sb_id_seq MINVALUE 0 START WITH 0;
CREATE SEQUENCE /*$wgDBprefix*/shoutbox_sb_id_seq MINVALUE 0 START WITH 0 increment by 1 nomaxvalue;
CREATE TABLE /*$wgDBprefix*/bs_shoutbox (
	sb_id           NUMBER NOT NULL,
	sb_page_id      NUMBER NOT NULL,        /* foreign key to page.page_id */
	sb_user_id      NUMBER NOT NULL,        /* foreign key to user.user_id */
	sb_timestamp    VARCHAR2(16) NOT NULL,       /* timestamp YmdHis */
	sb_user_name    VARCHAR2(255) NOT NULL,       /* foreign key to user.user_name */
	sb_message      LONG NOT NULL,
	sb_archived     NUMBER DEFAULT 0 NULL,
    sb_title        VARCHAR2(255) DEFAULT '',
	sb_touched      VARCHAR2(16) DEFAULT '',
	sb_parent_id    NUMBER DEFAULT 0
);
ALTER TABLE /*$wgDBprefix*/bs_shoutbox ADD CONSTRAINT /*$wgDBprefix*/shoutbox_pk PRIMARY KEY (sb_id);

/*$mw$*/
CREATE OR REPLACE TRIGGER /*$wgDBprefix*/shoutbox_sb_id_increment
BEFORE INSERT ON /*$wgDBprefix*/bs_shoutbox
FOR EACH ROW
BEGIN
	SELECT /*$wgDBprefix*/shoutbox_sb_id_seq.nextval INTO :new.sb_id from dual;
END;
/*$mw$*/