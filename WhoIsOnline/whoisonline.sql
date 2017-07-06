-- Database definition for WhoIsOnline
--
-- Part of BlueSpice MediaWiki
--
-- @author     Markus Glaser <glaser@hallowelt.com>

-- @package    BlueSpice_Extensions
-- @subpackage WhoIsOnline
-- @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
-- @filesource

CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_whoisonline (
      wo_id             bigint(30) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
      wo_user_id        int(30)    unsigned NOT NULL,
      wo_user_name      varbinary(255)      NOT NULL DEFAULT '',
      wo_user_real_name varbinary(255)      NOT NULL DEFAULT '',
      wo_page_id        int(10)    unsigned NOT NULL,
      wo_page_namespace int(11)             NOT NULL,
      wo_page_title     varbinary(255)      NOT NULL,
      wo_timestamp      int(11)    unsigned NOT NULL,
      wo_action         varbinary(32)       NOT NULL
) /*$wgDBTableOptions*/
COMMENT='BlueSpice: WhoIsOnline - Stores information on users activities';