<?php
	error_reporting(1);
	require_once('nusoap.php');

	/**
	* LinguLab live Webservice PHP Class
	* 
	* Class to get rating of text on PHP-Systems
	* 
	* @author Oliver Storm
	* @author Stefan Huissel
	* @author Stefan Huissel
	*/
	class linguLabLiveWebservice
	{
	private $_username = 'weichart@hallowelt.biz'; # Username	
	private $_pw = 'Hallowelt89'; # Password	

	private $_serviceURL = 'http://api.lingulab.de/LiveService.asmx?WSDL'; # URI of webservice

	private $_result; # saves login result
	private $_classClient; # Soap-Object
	public $resultMessage;
	public $loginData;

	private $_loginError = array(
								101 => 'Authentication failed',
								102 => 'Authentication session has already opened. You cannot open new session till old is not expired',
								103 => 'Too many login tries. Login tries limit was reached',
								300 => 'Internal error'
								);
	private $_authenticationError = array(
											111 => 'Authentication key is not valid',
											112 => 'Authentication key was expired',
											210 => 'Wrong parameter. Empty authentication key',
											310 => 'Internal error'
										);

	/**
	* Connects via SOAP to the Webservice and gets the authentication key
	* 
	* public constructor function
	* 
	* @return void
	*/
	public function __construct()
	{
		if($_GET['action']=="logout"){
			session_unset();
			session_destroy();
			$_SESSION = array();
			$this->resultMessage = "";
			$this->loginData = "";
		}	

		if(isset($_POST['username']) && $_POST['username']!="" && $this->_username==""){
			$this->_username = $_POST['username'];
			$this->_pw = $_POST['pass'];
		}

		if($this->_username!="") $_SESSION['username'] = $this->_username;			

		if(isset($_SESSION['username']) && $_SESSION['username']!=""){

			$this->_classClient = new soap_client($this->_serviceURL, true);

			$this->_result = $this->_classClient->call('Login', array(array('userName' => $this->_username,'password' => $this->_pw)));

			$err = $this->_classClient->getError();
			if ($this->_classClient->fault) {	
				echo '<h2>Error has occured while Login process.</h2>';
			}
			else if ($err){
				echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			}
			//if there is valid session -> revalidate
			if($this->_result['LoginResult']['ErrorCode'] == 101 ){
				//echo $this->_loginError[$this->_result['LoginResult']['ErrorCode']];
			}
			elseif($this->_result['LoginResult']['ErrorCode'] == 102 ){
				if($_SESSION['lingulabkey'] != ""){
					$sessionValidation = $this->_classClient->call('ValidateAuthentication', array(array('authenticationKey' => $_SESSION['lingulabkey'])));
					if($sessionValidation['ValidateAuthenticationResult'] == 0){
						//revalidate
						$this->_result['LoginResult']['AuthenticationKey'] = $_SESSION['lingulabkey'];
					}
					else{
						echo '->'.$this->_authenticationError[$sessionValidation['ValidateAuthenticationResult']]; 

					}
				}
				else{
					//echo "Error occured. Session ain't saved no more.";
				}
			}
			else{
					//for first login
				$_SESSION['lingulabkey'] = $this->_result['LoginResult']['AuthenticationKey'];

				if($_SESSION['lingulabkey']!=""){
						$this->loginData = "Eingeloggt als: " . $this->_result['LoginResult']['FullName'] . " (". $this->_result['LoginResult']['RoleName'] .")";
					$_SESSION['username'] = $this->_username;
				}

			}

				if($this->_result['LoginResult']['ErrorMessage']!=""){
				$this->resultMessage = $this->_result['LoginResult']['ErrorMessage'];
				}

		}

		//echo $this->resultMessage;
		//print_r($this->_result); 
		//echo $_SESSION['lingulabkey'];  
	}
	
	/**
	* Collects the different texttypes via SOAP from the Service
	* 
	* public configuration function
	* 
	* @return string
	*/
	public function getConfiguration()
	{
		$_options = $this->_classClient->call('GetConfigurations', array(array('authenticationKey'=>$_SESSION['lingulabkey'],'languageKey' => 'de')));
		$optString .= '<select name="mode" id="mode" class="dropdown" onchange="checkKeywordActivity()">';
		
		if($_options['GetConfigurationsResult']['Configurations']['ConfigurationEntry']['Id']!=""){
			/* Free User */
			$optString .=  '<option value="'.$_options['GetConfigurationsResult']['Configurations']['ConfigurationEntry']['Id'].','.$_options['GetConfigurationsResult']['Configurations']['ConfigurationEntry']['IsKeywordsSupported'].'">';
			$optString .=  $_options['GetConfigurationsResult']['Configurations']['ConfigurationEntry']['Name'];
			$optString .=  '</option>'; 
		}else{
			/* Premium User */
			foreach($_options['GetConfigurationsResult']['Configurations']['ConfigurationEntry'] as $opt){
				$optString .=  '<option value="'.$opt['Id'].','.$opt['IsKeywordsSupported'].'">';
				$optString .=  $opt['Name'];
				$optString .=  '</option>'; 
			}			
		}
		
		$optString .= '</select>';
		
		return $optString;
	}

	
	/**
	* Delievers the data to the Service and receives the result
	* 
	* @return void
	*/	
	public function checkContent($_POST)
	{
		$text = $_POST['text'];
		$h1 = isset($_POST['h1']) ? '<h1>'.$_POST['h1'].'</h1>' : '';
		$h2 = isset($_POST['h2']) ? '<h2>'.$_POST['h2'].'</h2>' : '';
		$h3 = isset($_POST['h3']) ? '<h3>'.$_POST['h3'].'</h3>' : '';
		$text = utf8_decode($h1).utf8_decode($h2).utf8_decode($h3).$text;
	
		$kw1 = isset($_POST['kw1']) ? $_POST['kw1'] : '';
		$kw2 = isset($_POST['kw2']) ? $_POST['kw2'] : '';
		$kw3 = isset($_POST['kw3']) ? $_POST['kw3'] : '';
		$configType = isset($_POST['mode']) ? $_POST['mode'] : '01_web-text_grundform';
	
		$aparam = array('inputData'=>array('Text' => $text,'ConfigurationId'=>$configType,'SearchKeyword1'=>$kw1,'SearchKeyword2'=>$kw2,'SearchKeyword3'=>$kw3),'authenticationKey'=>$_SESSION['lingulabkey']);
		$key = $_SESSION['lingulabkey'];
		$action = $this->_classClient->call('ProcessText', array($aparam));
	   
		$res = $action['ProcessTextResult'];
		//var_dump( $res );
		if($res['ErrorCode'] != 0){
			if($res['ErrorCode'] == 231)
				echo "Der eingegebene Text enthält weniger als 200 Zeichen.<br/><br/>";
			else
				echo $res['ErrorMessage'];
		}
		else{
			$_SESSION['resid'] = $res['ResultId'];
			//echo $res['MeasureStarsHtml'].'<br/>';
			$out = '{"text":"Messung (0-10): '.$res['Measure'].' \n';
			$out .= 'Details anzeigen?", ';
			$out .= '"link":"'.$res['LinkOnResultPage'].'"}';
			echo $out;//json_encode($out);
		}
	}


	public function GetUpdatedText(){
		$params = array('resultKey' => $_SESSION['resid'],'authenticationKey'=>$_SESSION['lingulabkey']);
		$content = $this->_classClient->call('GetUpdatedText', array($params));

		$h1 = $this->__get_string_between($content['GetUpdatedTextResult']['RawText'], "<h1>", "</h1>"); 
		$h2 = $this->__get_string_between($content['GetUpdatedTextResult']['RawText'], "<h2>", "</h2>"); 
		$h3 = $this->__get_string_between($content['GetUpdatedTextResult']['RawText'], "<h3>", "</h3>"); 
		$text = $this->delUnwantedTags($content['GetUpdatedTextResult']['RawText']); 

		echo $h1.'[,]'.$h2.'[,]'.$h3.'[,]'.$text;

	}

	public function __get_string_between($string, $start, $end){ 

		//Calculate the length of the start and end tags
		$lenStart = strlen($start);
		$lenEnd = strlen($end);
		$startTag = strpos($string, $start);
		///If there is no initial match to the $start string, return an empty string
		if ($startTag === false) return "";
		//Calculate the start tag position and the first end tag position
		$strStart = $startTag + $lenStart;
		$strEnd = strpos($string, $end);
		//Set a counter for the tags
		$tagCount = 0;
		//Use $test to see if there is another $start string after the first, but before the $strEnd position
		$test = strpos($string, $start, $strStart);
		//Use this while loop to check if there are other matching tags
		while($test !== false && $strEnd > $test) {
			$tagCount ++;
			$next = $test + $lenStart;
			$test = strpos($string, $start, $next);
		}
		//If there is more than one tag, calculate the new end tag position
		if ($tagCount) {
			for($i = 0; $i < $tagCount; $i++) {
				$strEnd = strpos($string, $end, $strEnd + $lenEnd);
			}
		}

		$tmp = $strEnd - $strStart;
		return substr($string, $strStart, $tmp);
	} 


	function delUnwantedTags ($code){
		$tags = array('h1','h2','h3');

		for($a = 0;$a <= count($tags);$a++){
			$code = preg_replace("#<".$tags[$a].".*</".$tags[$a].">#Ui", "", $code);
		}
		return $code;
	}

		public function checkLogin (){
			return ($this->_result['LoginResult']['ErrorMessage']=="") ? true : false;
		}

	}

