ALTER TABLE /*$wgDBprefix*/bs_review CHANGE `owner` `rev_owner` INT( 5 ) UNSIGNED NOT NULL;
CREATE INDEX /*i*/rev_owner ON /*$wgDBprefix*/bs_review (rev_owner);