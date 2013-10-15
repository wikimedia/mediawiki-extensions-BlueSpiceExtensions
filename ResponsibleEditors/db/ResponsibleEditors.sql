-- Database definition for ResponsibleEditors
--
-- Part of BlueSpice for MediaWiki
--
-- @author     Robert Vogel <vogel@hallowelt.biz>
-- @version    $Id$
-- @package    BlueSpice_Extensions
-- @subpackage ResponsibleEditors
-- @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
-- @filesource

CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_responsible_editors (
	re_page_id  int( 8 ) unsigned NOT NULL DEFAULT 0,
	re_user_id  int( 5 ) unsigned NOT NULL DEFAULT 0,
	re_position int( 5 ) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY ( re_page_id, re_user_id )
) /*$wgDBTableOptions*/
COMMENT='BlueSpice: Responsible Editors - Stores the users responsibilities for certain articles';