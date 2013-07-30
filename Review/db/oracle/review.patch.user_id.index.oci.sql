-- Add user_id column index
CREATE INDEX /*i*/user_id_idx ON /*$wgDBprefix*/bs_review_steps (revs_user_id);