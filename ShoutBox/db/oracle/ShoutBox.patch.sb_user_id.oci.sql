-- Add user_id for ShoutBox
ALTER TABLE /*$wgDBprefix*/bs_shoutbox ADD sb_user_id NUMBER NOT NULL;

CREATE INDEX /*i*/sb_user_id ON /*$wgDBprefix*/bs_shoutbox (sb_user_id);

