-- Add startdate column index
CREATE INDEX /*i*/startdate_idx ON /*$wgDBprefix*/bs_review (rev_startdate);