-- Add sb_page_id column index
ALTER TABLE /*$wgDBprefix*/bs_shoutbox 
  ADD INDEX (sb_page_id);