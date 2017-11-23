-- Database definition for SaferEdit
--
-- Part of BlueSpice MediaWiki
--
-- @author     Markus Glaser <glaser@hallowelt.com>

-- @package    BlueSpice_Extensions
-- @subpackage SaferEdit
-- @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
-- @filesource

CREATE SEQUENCE /*$wgDBprefix*/saferedit_se_id_seq MINVALUE 0 START WITH 0;
CREATE TABLE /*$wgDBprefix*/bs_saferedit (
	se_id				NUMBER NOT NULL,
	se_user_name		VARCHAR2(255),
	se_page_title		VARCHAR2(510),
	se_page_namespace	NUMBER NOT NULL,
	se_edit_section		NUMBER NOT NULL,
	se_timestamp		VARCHAR2(16) NOT NULL
);
ALTER TABLE /*$wgDBprefix*/bs_saferedit ADD CONSTRAINT /*$wgDBprefix*/bs_saferedit_pk PRIMARY KEY (se_id);

/*$mw$*/
CREATE OR REPLACE TRIGGER /*$wgDBprefix*/bs_saferedit_se_id_inc
BEFORE INSERT ON /*$wgDBprefix*/bs_saferedit
FOR EACH ROW
BEGIN
	SELECT /*$wgDBprefix*/saferedit_se_id_seq.nextval INTO :NEW.se_id FROM dual;
END;
/*$mw$*/