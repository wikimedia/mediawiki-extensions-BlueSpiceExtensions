-- Database definition for PermissionManager
--
-- Part of BlueSpice for MediaWiki
--
-- @author     Sebastian Ulbricht <sebastian.ulbricht@gmx.de>
-- @version    $Id: PermissionManager.oci.sql 9795 2013-06-18 14:40:59Z rvogel $
-- @package    BlueSpice_Extensions
-- @subpackage PermissionManager
-- @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
-- @filesource

CREATE SEQUENCE /*$wgDBprefix*/pm_tmpl_id_seq MINVALUE 0 START WITH 0;
CREATE TABLE /*$wgDBprefix*/bs_permission_templates
(
  tpl_id            number NOT NULL,
  tpl_name          varchar2(100) NOT NULL,
  tpl_description   varchar2(1000),
  tpl_data          long NOT NULL
);
ALTER TABLE /*$wgDBprefix*/bs_permission_templates ADD CONSTRAINT /*$wgDBprefix*/bs_permission_pk PRIMARY KEY (tpl_id);

/*$mw$*/
CREATE OR REPLACE TRIGGER /*$wgDBprefix*/pm_tmpl_id_inc
BEFORE INSERT ON /*$wgDBprefix*/bs_permission_templates
FOR EACH ROW
BEGIN
	SELECT /*$wgDBprefix*/pm_tmpl_id_seq.nextval INTO :NEW.tpl_id FROM dual;
END;
/*$mw$*/