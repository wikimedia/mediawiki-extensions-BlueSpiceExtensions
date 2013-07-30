ALTER TABLE /*$wgDBprefix*/bs_review_steps CHANGE `user_id` `revs_user_id` SMALLINT( 6 ) NOT NULL DEFAULT '0';
CREATE INDEX /*i*/revs_user_id ON /*$wgDBprefix*/bs_review_steps (revs_user_id);