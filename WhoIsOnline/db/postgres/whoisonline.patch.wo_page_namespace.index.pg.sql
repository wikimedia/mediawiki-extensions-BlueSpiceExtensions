-- Add page_namespace column index
CREATE INDEX /*i*/wo_page_namespace ON /*$wgDBprefix*/bs_whoisonline (wo_page_namespace);