<?php
class PasteImage extends BsExtensionMW {

		private static $aStashedFiles = array();

		public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['PasteImage'] = dirname( __FILE__ ) . '/PasteImage.i18n.php';

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'PasteImage',
			EXTINFO::DESCRIPTION => 'PasteImage',
			EXTINFO::AUTHOR      => 'PasteImage',
			EXTINFO::VERSION     => '1.22.0 ($Rev: 5559 $)',
			EXTINFO::STATUS      => 'alpha',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array('bluespice' => '1.22.0')
		);
		$this->mExtensionKey = 'MW::PasteImage';

		BsExtensionManager::setContext( 'MW::PasteImage' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	protected function initExt() {
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler('PasteImage', $this, 'pasteImageUpload', 'edit');
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler('PasteImage', $this, 'pasteImageCheckImagename', 'edit');
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler('PasteImage', $this, 'pasteImageRemoveImage', 'edit');

		$this->registerScriptFiles( BsConfig::get( 'MW::ScriptPath' ).'/extensions/BlueSpiceExtensions/PasteImage/js', 'PasteImage', true, false, false, 'MW::PasteImage' );
		$this->registerScriptFiles( BsConfig::get( 'MW::ScriptPath' ).'/extensions/BlueSpiceExtensions/PasteImage/js/lib', 'javaVersion', true, false, true, 'MW::PasteImage' );
		BsStyleManager::add( 'PasteImage', BsConfig::get( 'MW::ScriptPath' ).'/extensions/BlueSpiceExtensions/PasteImage/css/PasteImage.css' );

		// Hooks
		$this->setHook( 'EditPage::showEditForm:fields', 'addPasteImageApplet');
		$this->setHook( 'ArticleSave' );
		$this->setHook( 'VisualEditorConfig' );
		$this->bStashUploaded = false;

		/*if (!file_exists("extensions/BlueSpiceFoundation/data/pasteImage"))
			mkdir ("extensions/BlueSpiceFoundation/data/pasteImage");*/
	}

		private static function uploadStash($sKey){
		   global $wgLocalFileRepo, $wgUser, $wgRequest;
			$oUploadStash = new UploadStash(new LocalRepo($wgLocalFileRepo));
			$oUploadFromStash = new UploadFromStash($wgUser, $oUploadStash, $wgLocalFileRepo);
			//$sessionKey = $wgRequest->getText( 'wpSessionKey' );
			//$sessionData = $wgRequest->getSessionData( UploadBase::SESSION_KEYNAME );
			$oUploadFromStash->initialize($sKey, $sKey); //$sessionKey, $sessionData[$sKey], true);
			$status = $oUploadFromStash->performUpload("", "", false, $wgUser);
			$oUploadFromStash->cleanupTempFile();
			if (file_exists("extensions/BlueSpiceFoundation/data/pasteImage/".$sKey))
				unlink ("extensions/BlueSpiceFoundation/data/pasteImage/".$sKey);
			return $status->ok;
		}

		public function pasteImageCheckImagename(&$sOutput){
			$aResult = array();
			$sImageName = trim(BsCore::getParam( 'name', "",  BsPARAMTYPE::STRING|BsPARAM::REQUEST ));
			if (strpos($sImageName, ".") === false)
					$sImageName .= ".dummy";
			if ($sImageName == ""){
				$aResult['success'] = "false";
				$aResult['status'] = "error";
				$aResult['message'] = wfMsg('bs-pasteImage-empty_name');
				$sOutput = json_encode($aResult);
				return true;
			}
			global $wgLocalFileRepo;
			$oUploadStash = new UploadStash(new LocalRepo($wgLocalFileRepo));
			try{
				$result = $oUploadStash->getFile($sImageName);
				if (is_object($result) && !empty($result)){
					$aResult['success'] = "false";
					$aResult['status'] = "error";
					$aResult['message'] = wfMsg('bs-pasteImage-name_already_taken');
					$sOutput = json_encode($aResult);
					return true;
				}
			} catch (UploadStashFileNotFoundException $e){}
			catch (UploadStashBadPathException $e){
				$aResult['success'] = "false";
				$aResult['status'] = "error";
				$aResult['message'] = wfMsg('bs-pasteImage-name_format_wrong');
				$sOutput = json_encode($aResult);
				return true;
			}
			$oTitle = Title::newFromText($sImageName, NS_FILE);
			if ($oTitle->exists()){
				$aResult['success'] = "false";
				$aResult['status'] = "error";
				$aResult['message'] =wfMsg('bs-pasteImage-name_already_taken');
				$sOutput = json_encode($aResult);
				return true;
			}
			$sOutput = json_encode(array('success' => "true"));
			return true;
		}

		/*'ArticleSave': before an article is saved
			$article: the article (object) being saved
			$user: the user (object) saving the article
			$text: the new article text
			$summary: the article summary (comment)
			$isminor: minor flag
			$iswatch: watch flag
			$section: section #
		 */

		public function onArticleSave(&$oArticle, &$oUser, &$sText, &$sSummary, $bIsMinor, $bIsWatch, $sSection){
			$sText = preg_replace_callback("#<pasteImage>(.*?)</pasteImage>#is", "PasteImage::replaceTags", $sText);
			return true;
		}

		public static function replaceTags($aMatches){
			self::uploadStash($aMatches[1]);
			self::$aStashedFiles [$aMatches[1]] = "";
			$oTitle = Title::newFromText($aMatches[1]);
			$sOut = "[["/*.MWNamespace::getCanonicalName($oTitle->getNamespace())*/."Datei".":".$oTitle->getText()."]]";
			return $sOut;

		}

		public function pasteImageRemoveImage(&$sOutput){
			$sFilename = BsCore::getParam( 'name', 0,  BsPARAMTYPE::STRING|BsPARAM::REQUEST );
			global $wgLocalFileRepo, $wgRequest;
			$oUploadStash = new UploadStash(new LocalRepo($wgLocalFileRepo));
			$sessionData = $wgRequest->getSessionData( UploadBase::SESSION_KEYNAME );
			$oFile = $oUploadStash->getFile($sFilename);
			$oUploadStashFile = new UploadStashFile($oUploadStash, new LocalRepo($wgLocalFileRepo), $oFile->getPath(), $sFilename, $sessionData[$sFilename] );
			$sOutput = $oUploadStashFile->remove();
			return true;
		}

		public function pasteImageUpload(&$sOutput){
			$sImg = BsCore::getParam( 'img', 0,  BsPARAMTYPE::STRING|BsPARAM::REQUEST );
			$sName = BsCore::getParam( 'name', 0,  BsPARAMTYPE::STRING|BsPARAM::REQUEST );
			$data = base64_decode($sImg);
			if (empty($data)){
				$aResult ['success'] = "false";
				$aResult ['status'] = "error";
				$aResult ['code'] = "2";
				$sOutput = json_encode($aResult);
				return true;
			}
			$im = imagecreatefromstring($data);

			$mimetype = $this->getImageMimeType($data);
			if ($mimetype != "png" && $mimetype != "gif")
				$mimetype = "jpeg";

			if (strpos($sName, ".") === false){
				$sKey = $sName.".".$mimetype;
			}
			else{
				$aName = explode(".", $sName);
				switch($aName[count($aName)-1]){
					case "png":
						$mimetype = "png";
						break;
					case "gif":
						$mimetype = "gif";
						break;
					default:
						$mimetype = "jpeg";
						break;
				}
				$sKey = str_replace($aName[count($aName)-1], $mimetype, $sName);
			}

			if ($im !== false) {
				$mimetype = "image/". $mimetype;
				header('Content-Type: ' . $mimetype);
				global $IP;
				if ($mimetype == "image/png"){
					imagealphablending($im, false);
					imagesavealpha($im, true);
					imagepng($im, "extensions/BlueSpiceFoundation/data/pasteImage/".$sKey);
				}
				else if ($mimetype == "image/gif")
					imagegif($im, "extensions/BlueSpiceFoundation/data/pasteImage/".$sKey);
				else 
					imagejpeg($im, $IP."/extensions/BlueSpiceFoundation/data/pasteImage/".$sKey); 

				imagedestroy($im);
			}
			global $wgLocalFileRepo;

			$oUploadStash = new UploadStash(new LocalRepo($wgLocalFileRepo));
			// MW1.20 Parameter geändert
			$oFile = $oUploadStash->stashFile("extensions/BlueSpiceFoundation/data/pasteImage/".$sKey, null);
			$oThumbPreview = $oFile->transform(array("width"=>100)); //getThumbnail(100);
			if ($oFile->getSize() > SpecialUploadStash::MAX_SERVE_BYTES){
				$oThumb500 = $oFile->transform(array("width"=>500)); //getThumbnail(500);
			}

			if ($oFile == null){
				$aResult ['success'] = "false";
				$aResult ['status'] = "error";
				$aResult ['message'] = wfMsg('bs-pasteImage-failed_stash');
				$sOutput = json_encode($aResult);
				return true;
			}
			self::$aStashedFiles [$sKey] = "";
			$aResult ['success'] = "true";
			$aResult ['name'] = $sKey;
			$aResult ['fullurl'] = $oFile->getFullUrl();
			$aResult ['previewurl'] = $oThumbPreview->getUrl();
			$aResult ['thumburl'] = isset($oThumb500) ? $oThumb500->getUrl() : "";
			$sOutput = json_encode($aResult);
			return true;
		}

		public function onVisualEditorConfig(&$aConfigStandard, &$aConfigOverwrite){
			$aConfigStandard ['theme_advanced_buttons2'] [] = '|';
			$aConfigStandard ['theme_advanced_buttons2'] [] = 'hwpasteImage';
			return true;
		}

		private function getBytesFromHexString($hexdata){
			for($count = 0; $count < strlen($hexdata); $count+=2)
				$bytes[] = chr(hexdec(substr($hexdata, $count, 2)));
			return implode($bytes);
		}

		private function getImageMimeType($imagedata){
			$imagemimetypes = array( 
				"jpeg" => "FFD8", 
				"png" => "89504E470D0A1A0A", 
				"gif" => "474946",
				"bmp" => "424D", 
				"tiff" => "4949",
				"tiff" => "4D4D"
			);
			foreach ($imagemimetypes as $mime => $hexbytes){
				$bytes = $this->getBytesFromHexString($hexbytes);
				if (substr($imagedata, 0, strlen($bytes)) == $bytes)
					return $mime;
			}
			return NULL;
		}

		/*'EditPage::showEditForm:fields': allows injection of form field into edit form
		&$editor: the EditPage instance for reference
		&$out: an OutputPage instance to write to
		return value is ignored (should always return true)*/
		public function addPasteImageApplet(&$editor, &$out){
			global $wgDBname, $wgScriptPath, $wgDBprefix, $wgEnableUploads;
			if ( !$this->mAdapter->User->isAllowed( 'edit' ) )
				return true;
			if ($wgEnableUploads != true)
				return true;
			//$sApplet = '<applet id="pasteImageApplet" name="pasteImage" codebase="'.BsConfig::get( 'MW::ScriptPath' ).'/extensions/BlueSpiceExtensions/PasteImage/vendor/" archive="PasteImage.jar" code="biz.hallowelt.PasteImage.class" width="100" height="100" MAYSCRIPT></applet>';
			$sApplet = '<applet id="pasteImageApplet" name="pasteImage" archive="'.BsConfig::get( 'MW::ScriptPath' ).'/extensions/BlueSpiceExtensions/PasteImage/vendor/PasteImage.jar?'.time().'" code="biz.hallowelt.PasteImage.class" width="100" height="100" MAYSCRIPT></applet>';
			$sImageBox = '<div id="pastedImages"><div id="pasteImage_left"></div><div id="images"></div><div id="pasteImage_right"></div></div>';
			$out->addHtml('<div id="pasteImage">'.$sApplet.$sImageBox.'</div>');
			$out->addHtml('<div id="pasteImage_loader"></div>');
			$oCurrentUser = $this->mAdapter->get('User');
			if ( $oCurrentUser->isLoggedIn() === false ) return true;
			$sCookiePrefix = $wgDBname;
			if ($wgDBprefix != "")
				$sCookiePrefix .= "_".$wgDBprefix;
			setcookie ( $sCookiePrefix."_session", $_COOKIE[$sCookiePrefix."_session"], 0, $wgScriptPath);
			setcookie ( $sCookiePrefix."UserID", $_COOKIE[$sCookiePrefix."UserID"], 0, $wgScriptPath);
			setcookie ( $sCookiePrefix."UserName", $_COOKIE[$sCookiePrefix."UserName"], 0, $wgScriptPath);
			return true;
		}
}