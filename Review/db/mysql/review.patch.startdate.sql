ALTER TABLE /*$wgDBprefix*/bs_review CHANGE `startdate` `rev_startdate` DATE NOT NULL DEFAULT '2000-01-01';
CREATE INDEX /*i*/rev_startdate ON /*$wgDBprefix*/bs_review (rev_startdate);