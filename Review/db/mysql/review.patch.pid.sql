ALTER TABLE /*$wgDBprefix*/bs_review CHANGE `pid` `rev_pid` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0';
CREATE INDEX /*i*/rev_pid ON /*$wgDBprefix*/bs_review (rev_pid);