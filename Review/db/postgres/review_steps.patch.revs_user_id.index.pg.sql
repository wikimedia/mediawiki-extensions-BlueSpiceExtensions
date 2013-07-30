-- Add user_id column index
CREATE INDEX /*i*/revs_user_id ON /*$wgDBprefix*/bs_review_steps (revs_user_id);