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

CREATE TABLE /*$wgDBprefix*/bs_review (
  rev_id         serial          NOT NULL PRIMARY KEY,
  rev_pid        smallint        NOT NULL DEFAULT '0',
  rev_editable   smallint        NOT NULL DEFAULT '0',
  rev_sequential smallint        NOT NULL DEFAULT '0',
  rev_abortable  smallint        NOT NULL DEFAULT '0',
  rev_startdate  varchar(32)     NOT NULL DEFAULT '2000-01-01 00:00:00+00',
  rev_enddate    varchar(32)     NOT NULL DEFAULT '2000-01-01 00:00:00+00',
  rev_owner      smallint        NOT NULL
);

CREATE TABLE /*$wgDBprefix*/bs_review_steps (
  revs_id        serial          NOT NULL PRIMARY KEY,
  revs_review_id smallint        NOT NULL DEFAULT '0',
  revs_user_id   smallint        NOT NULL DEFAULT '0',
  revs_status    smallint        NOT NULL DEFAULT '-1',
  revs_sort_id   smallint        NOT NULL DEFAULT '0',
  revs_comment   varchar(255)    DEFAULT NULL,
  revs_timestamp timestamp with time zone,
  revs_delegate_to smallint		NOT NULL DEFAULT '0'
);

CREATE TABLE /*$wgDBprefix*/bs_review_templates (
  revt_id        serial          NOT NULL PRIMARY KEY,
  revt_name      varchar(255)    NOT NULL,
  revt_owner     int             NOT NULL,
  revt_user      varchar(255)    NOT NULL,
  rev_editable   smallint        NOT NULL,
  rev_sequential smallint        NOT NULL,
  rev_abortable  smallint        NOT NULL,
  revt_public    smallint        NOT NULL
);

/* CR RBV (30.06.11 14:55): Spaltennamen prefixen. MediaWiki-konforme Typen verwenden */
