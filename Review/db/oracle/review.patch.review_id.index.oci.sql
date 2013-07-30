-- Add review_id column index
CREATE INDEX /*i*/review_id_idx ON /*$wgDBprefix*/bs_review_steps (revs_review_id);