-- Database definitions for InsertFile
--
-- Part of BlueSpice MediaWiki
--
-- @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-network.hk>

-- @package    BlueSpice_Extensions
-- @subpackage InsertFile
-- @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
-- @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
-- @filesource

/*$mw$*/
CREATE OR REPLACE FUNCTION bs_timestamp2unix(indate timestamp) 
RETURN NUMBER  
IS 
  n_result NUMBER; 
BEGIN 
     SELECT DISTINCT unix_with_cast  INTO n_result 
      FROM 
      ( 
        SELECT  
        ROUND (  (  CAST (CURRENT_TIMESTAMP AS DATE) 
                 - TO_DATE ('01-01-1970', 'DD.MM.YYYY HH24:MI:SS') 
                ) 
              * 86400, 
              0 
        ) unix_with_cast 
        FROM dual  

      );  
  RETURN n_result; 
END;
/*$mw$*/

/*$mw$*/
CREATE OR REPLACE function if_getImagePosition
(filename in varchar2)
return number
is
row number;
cursor c1 is
	SELECT img_name
	FROM /*$wgDBprefix*/image
	WHERE (img_major_mime = 'image' OR img_minor_mime = 'tiff')
	ORDER BY img_name ASC;
begin
	row := 0;

	FOR img_rec in c1
	LOOP
		row := row + 1;

		IF img_rec.img_name = filename THEN
			RETURN(row);
		END IF;

	END LOOP;
return(1);
end;
/*$mw$*/

/*$mw$*/
CREATE OR REPLACE function if_getFilePosition
(filename in varchar2)
return number
is
row number;
cursor c1 is
	SELECT img_name
	FROM /*$wgDBprefix*/image
	WHERE (img_major_mime != 'image' OR img_minor_mime != 'tiff')
	ORDER BY img_name ASC;
begin
	row := 0;

	FOR img_rec in c1
	LOOP
		row := row + 1;

		IF img_rec.img_name = filename THEN
			RETURN(row);
		END IF;

	END LOOP;
return(1);
end;
/*$mw$*/

/*$mw$*/
CREATE OR REPLACE function if_getImageUploadPosition
return number
is
row number;
filename varchar2(255);
cursor c1 is
	SELECT img_name
	FROM /*$wgDBprefix*/image
	WHERE (img_major_mime = 'image' OR img_minor_mime = 'tiff')
	ORDER BY img_name ASC;
begin
	row := 0;
	SELECT img_name INTO filename
	FROM /*$wgDBprefix*/image
	WHERE (img_major_mime = 'image' OR img_minor_mime = 'tiff')
	ORDER BY img_timestamp DESC
	LIMIT 1;

	FOR img_rec in c1
	LOOP
		row := row + 1;

		IF img_rec.img_name = filename THEN
			RETURN(row);
		END IF;

	END LOOP;
return(1);
end;
/*$mw$*/

/*$mw$*/
CREATE OR REPLACE function if_getFileUploadPosition
return number
is
row number;
filename varchar2(255);
cursor c1 is
	SELECT img_name
	FROM /*$wgDBprefix*/image
	WHERE (img_major_mime != 'image' OR img_minor_mime != 'tiff')
	ORDER BY img_name ASC;
begin
	row := 0;
	SELECT img_name INTO filename
			FROM /*$wgDBprefix*/image
			WHERE (img_major_mime != 'image' OR img_minor_mime != 'tiff')
			ORDER BY img_timestamp DESC
			LIMIT 1;

	FOR img_rec in c1
	LOOP
		row := row + 1;

		IF img_rec.img_name = filename THEN
			RETURN(row);
		END IF;

	END LOOP;
return(1);
end;
/*$mw$*/