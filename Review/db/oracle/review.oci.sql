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

CREATE SEQUENCE /*$wgDBprefix*/review_id_seq MINVALUE 0 START WITH 0;
CREATE TABLE /*$wgDBprefix*/bs_review (
	rev_id number NOT NULL,
	rev_pid number NOT NULL,
	rev_editable number,
        rev_sequential number,
        rev_abortable number,
	rev_startdate varchar2(14) NOT NULL,
	rev_enddate varchar2(14) NOT NULL,
	rev_owner number NOT NULL
);
ALTER TABLE /*$wgDBprefix*/bs_review ADD CONSTRAINT /*$wgDBprefix*/bs_review_pk PRIMARY KEY (rev_id);

/*$mw$*/
CREATE OR REPLACE TRIGGER /*$wgDBprefix*/review_id_inc
BEFORE INSERT ON /*$wgDBprefix*/bs_review
FOR EACH ROW
BEGIN
	SELECT /*$wgDBprefix*/review_id_seq.nextval INTO :NEW.rev_id FROM dual;
END;
/*$mw$*/

CREATE SEQUENCE /*$wgDBprefix*/review_steps_id_seq MINVALUE 0 START WITH 0;
CREATE TABLE /*$wgDBprefix*/bs_review_steps (
	revs_id        number(8) NOT NULL,
	revs_review_id number(8) NOT NULL,
	revs_user_id   number(8) NOT NULL,
	revs_status    number(8) NOT NULL,
	revs_sort_id   number(8) NOT NULL,
	revs_comment   varchar2(255),
	revs_timestamp timestamp(6) DEFAULT SYSTIMESTAMP,
	revs_delegate_to NUMBER DEFAULT 0
);
ALTER TABLE /*$wgDBprefix*/bs_review_steps ADD CONSTRAINT /*$wgDBprefix*/bs_review_steps_pk PRIMARY KEY (revs_id);

/*$mw$*/
CREATE OR REPLACE TRIGGER /*$wgDBprefix*/review_steps_id_inc
BEFORE INSERT ON /*$wgDBprefix*/bs_review_steps
FOR EACH ROW
BEGIN
	SELECT /*$wgDBprefix*/review_steps_id_seq.nextval INTO :NEW.revs_id FROM dual;
END;
/*$mw$*/

CREATE SEQUENCE /*$wgDBprefix*/review_tmpl_id_seq MINVALUE 0 START WITH 0;
CREATE TABLE /*$wgDBprefix*/bs_review_templates (
	revt_id        number NOT NULL,
	revt_name      varchar2(255) NOT NULL,
	revt_owner     number NOT NULL,
	revt_user      varchar2(255) NOT NULL,
	rev_editable   number,
        rev_sequential number,
        rev_abortable  number,
	revt_public    number NOT NULL
);
ALTER TABLE /*$wgDBprefix*/bs_review_templates ADD CONSTRAINT /*$wgDBprefix*/bs_review_tmpl_pk PRIMARY KEY (revt_id);

/*$mw$*/
CREATE OR REPLACE TRIGGER /*$wgDBprefix*/review_tmpl_id_inc
BEFORE INSERT ON /*$wgDBprefix*/bs_review_templates
FOR EACH ROW
BEGIN
	SELECT /*$wgDBprefix*/review_tmpl_id_seq.nextval INTO :NEW.revt_id FROM dual;
END;
/*$mw$*/


