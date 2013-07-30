-- Add se_page_namespace column index
CREATE INDEX /*i*/se_page_namespace ON /*$wgDBprefix*/bs_saferedit (se_page_namespace);