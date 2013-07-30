-- Add status column index
CREATE INDEX /*i*/revs_status ON /*$wgDBprefix*/bs_review_steps (revs_status);