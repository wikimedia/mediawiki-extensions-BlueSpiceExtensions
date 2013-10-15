-- Database definition for WhoIsOnline
--
-- Part of BlueSpice for MediaWiki
--
-- @author     Markus Glaser <glaser@hallowelt.biz>

-- @package    BlueSpice_Extensions
-- @subpackage WhoIsOnline
-- @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
-- @filesource

CREATE SEQUENCE /*$wgDBprefix*/whoisonline_wo_id_seq MINVALUE 0 START WITH 0;
CREATE TABLE /*$wgDBprefix*/bs_whoisonline (
	wo_id             NUMBER NOT NULL,
	wo_user_id        NUMBER NOT NULL,
	wo_user_name      VARCHAR2(255),
	wo_user_real_name VARCHAR2(512),
	wo_page_id        NUMBER NOT NULL,
	wo_page_namespace NUMBER NOT NULL,
	wo_page_title     VARCHAR2(255) NOT NULL,
	wo_timestamp      NUMBER NOT NULL,
	wo_action         VARCHAR2(32) NOT NULL
);
ALTER TABLE /*$wgDBprefix*/bs_whoisonline ADD CONSTRAINT /*$wgDBprefix*/bs_whoisonline_pk PRIMARY KEY (wo_id);

/*$mw$*/
CREATE OR REPLACE TRIGGER /*$wgDBprefix*/bs_wo_id_inc
BEFORE INSERT ON /*$wgDBprefix*/bs_whoisonline
FOR EACH ROW
BEGIN
	SELECT /*$wgDBprefix*/whoisonline_wo_id_seq.nextval INTO :NEW.wo_id FROM dual;
END;
/*$mw$*/
