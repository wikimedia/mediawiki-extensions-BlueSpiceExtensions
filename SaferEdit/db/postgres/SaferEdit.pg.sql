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

/* CREATE SEQUENCE se_id_seq MINVALUE 1 START WITH 1; */
CREATE TABLE /*$wgDBprefix*/bs_saferedit (
  se_id             serial         NOT NULL PRIMARY KEY,
  se_user_name      varchar(255)   default NULL,             /* foreign key to user.user_name */
  se_page_title     text           default NULL,             /* foreign key to page.page_title */
  se_page_namespace int            default 0,                /* foreign key to page.page_namespaec */
  se_edit_section   int            default -1,
  se_timestamp      varchar(16)    default NULL             /* YmdHis */
);