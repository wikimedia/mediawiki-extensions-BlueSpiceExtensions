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

CREATE TABLE /*$wgDBprefix*/bs_responsible_editors (
  re_page_id  NUMERIC NOT NULL,
  re_user_id  NUMERIC NOT NULL,
  re_position NUMERIC NULL
);
CREATE UNIQUE INDEX /*$wgDBprefix*/re_unique ON /*$wgDBprefix*/bs_responsible_editors(re_page_id, re_user_id);
