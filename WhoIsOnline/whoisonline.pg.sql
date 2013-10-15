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

CREATE TABLE /*$wgDBprefix*/bs_whoisonline (
      wo_id             serial NOT NULL PRIMARY KEY,
      wo_user_id        int    NOT NULL,
      wo_user_name      text   NOT NULL DEFAULT '',
      wo_user_real_name text   NOT NULL DEFAULT '',
      wo_page_id        int    NOT NULL,
      wo_page_namespace int    NOT NULL,
      wo_page_title     text   NOT NULL,
      wo_timestamp      int    NOT NULL,
      wo_action         text   NOT NULL
);