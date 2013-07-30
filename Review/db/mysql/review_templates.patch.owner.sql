ALTER TABLE /*$wgDBprefix*/bs_review_templates CHANGE `owner` `revt_owner` INT( 5 ) UNSIGNED NOT NULL;
CREATE INDEX /*i*/revt_owner ON /*$wgDBprefix*/bs_review_templates (revt_owner);