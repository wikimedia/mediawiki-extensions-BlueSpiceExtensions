-- Add name column index
CREATE INDEX /*i*/name_idx ON /*$wgDBprefix*/bs_review_templates (revt_name);