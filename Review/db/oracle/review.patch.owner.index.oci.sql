-- Add owner column index
CREATE INDEX /*i*/owner_idx ON /*$wgDBprefix*/bs_review (rev_owner);
CREATE INDEX /*i*/owner_2_idx ON /*$wgDBprefix*/bs_review_templates (revt_owner);