-- Add timestamp column index
ALTER TABLE /*$wgDBprefix*/bs_whoisonline 
  ADD INDEX (wo_timestamp);