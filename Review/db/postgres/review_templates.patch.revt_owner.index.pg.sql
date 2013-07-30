-- Add owner column index
CREATE INDEX /*i*/revt_owner ON /*$wgDBprefix*/bs_review_templates (revt_owner);