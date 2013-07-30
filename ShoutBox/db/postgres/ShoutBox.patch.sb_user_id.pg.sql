BEGIN;

ALTER TABLE bs_shoutbox 
  ADD sb_user_id int unsigned NOT NULL default 0,

CREATE INDEX /*i*/sb_user_id ON /*$wgDBprefix*/bs_shoutbox (sb_user_id);

COMMIT;
