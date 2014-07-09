-- Database definition for Review
--
-- Part of BlueSpice for MediaWiki
--
-- @author     Markus Glaser <glaser@hallowelt.biz>

-- @package    BlueSpice_Extensions
-- @subpackage Review
-- @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
-- @filesource

CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_review (
  rev_id         smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  rev_pid        smallint(5) unsigned NOT NULL DEFAULT '0',
  rev_editable   tinyint(3) unsigned  NOT NULL DEFAULT '0',
  rev_sequential tinyint(3) unsigned  NOT NULL DEFAULT '0',
  rev_abortable  tinyint(3) unsigned  NOT NULL DEFAULT '0',
  rev_startdate  date                 NOT NULL DEFAULT '2000-01-01',
  rev_enddate    date                 NOT NULL DEFAULT '2000-01-01',
  rev_owner      int(5) unsigned      NOT NULL,
  PRIMARY KEY (rev_id),
  UNIQUE KEY rev_id (rev_id)
) /*$wgDBTableOptions*/;

CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_review_steps (
  revs_id        smallint(5)  unsigned NOT NULL AUTO_INCREMENT,
  revs_review_id smallint(5)  unsigned NOT NULL DEFAULT '0',
  revs_user_id   smallint(6)           NOT NULL DEFAULT '0',
  revs_status    tinyint(4)            NOT NULL DEFAULT '-1',
  revs_sort_id   tinyint(3)   unsigned NOT NULL DEFAULT '0',
  revs_comment   varchar(255)          DEFAULT NULL,
  revs_delegate_to   smallint(6)       NOT NULL DEFAULT '0',
  revs_timestamp timestamp,
  PRIMARY KEY (revs_id),
  UNIQUE KEY revs_id (revs_id)
) /*$wgDBTableOptions*/;

CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/bs_review_templates (
  revt_id         int(10) unsigned     NOT NULL AUTO_INCREMENT,
  revt_name       varchar(255)         NOT NULL,
  revt_owner      int(5) unsigned      NOT NULL,
  revt_user       varchar(255)         NOT NULL,
  revt_editable   tinyint(3) unsigned  NOT NULL DEFAULT '0',
  revt_sequential tinyint(3) unsigned  NOT NULL DEFAULT '0',
  revt_abortable  tinyint(3) unsigned  NOT NULL DEFAULT '0',
  revt_public     tinyint(1)           NOT NULL,
  PRIMARY KEY (revt_id)
) /*$wgDBTableOptions*/;