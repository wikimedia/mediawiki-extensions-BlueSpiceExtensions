-- Database definition for PermissionManager
--
-- Part of BlueSpice for MediaWiki
--
-- @author     Sebastian Ulbricht <sebastian.ulbricht@gmx.de>

-- @package    BlueSpice_Extensions
-- @subpackage PermissionManager
-- @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
-- @filesource

CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_permission_templates (
  `tpl_id` int(10) unsigned NOT NULL auto_increment,
  `tpl_name` varchar(100) collate utf8_bin NOT NULL,
  `tpl_data` blob NOT NULL,
  `tpl_description` text collate utf8_bin NOT NULL,
  PRIMARY KEY  (`tpl_id`)
)/*$wgDBTableOptions*/;
