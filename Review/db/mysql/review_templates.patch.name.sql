ALTER TABLE /*$wgDBprefix*/bs_review_templates CHANGE `name` `revt_name` VARBINARY( 255 ) NOT NULL;
CREATE INDEX /*i*/revt_name ON /*$wgDBprefix*/bs_review_templates (revt_name);