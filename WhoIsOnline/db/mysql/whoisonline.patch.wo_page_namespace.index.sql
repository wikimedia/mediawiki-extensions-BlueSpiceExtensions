-- Add page_namespace column index
ALTER TABLE /*$wgDBprefix*/bs_whoisonline 
  ADD INDEX (wo_page_namespace);