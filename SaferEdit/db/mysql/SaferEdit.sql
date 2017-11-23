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

CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_saferedit (
  se_id             int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  se_user_name      varchar(255)     default NULL,             /* foreign key to user.user_name */
  se_page_title     varbinary(255)   default NULL,             /* foreign key to page.page_title */
  se_page_namespace int(11)          default 0,                /* foreign key to page.page_namespaec */
  se_edit_section   int(10)          default -1,
  se_timestamp      varchar(16)      default NULL             /* YmdHis */
)/*$wgDBTableOptions*/;