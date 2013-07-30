ALTER TABLE /*$wgDBprefix*/bs_review_steps CHANGE `status` `revs_status` TINYINT( 4 ) NOT NULL DEFAULT '-1';
CREATE INDEX /*i*/revs_status ON /*$wgDBprefix*/bs_review_steps (revs_status);