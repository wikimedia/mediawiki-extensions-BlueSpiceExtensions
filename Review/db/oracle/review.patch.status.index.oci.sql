-- Add status column index
CREATE INDEX /*i*/status_idx ON /*$wgDBprefix*/bs_review_steps (revs_status);