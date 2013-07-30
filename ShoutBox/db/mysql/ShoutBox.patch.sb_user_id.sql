-- Add user_id for ShoutBox
ALTER TABLE /*$wgDBprefix*/bs_shoutbox 
	-- user_id of the shouting user
	ADD sb_user_id int unsigned NOT NULL default 0,
	ADD INDEX /*i*/sb_user_id (sb_user_id);
