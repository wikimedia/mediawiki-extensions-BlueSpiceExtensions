-- Add review_id column index
CREATE INDEX /*i*/revs_review_id ON /*$wgDBprefix*/bs_review_steps (revs_review_id);