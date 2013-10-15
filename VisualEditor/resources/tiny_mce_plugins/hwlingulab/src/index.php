<?php 	
    session_start();
	require_once('lingulablive.func.php');
	$linguLabLive = new linguLabLiveWebservice();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>711media - Webservice PHP Demo</title>
<link href="_css/main.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="ck/ckeditor.js"></script>
<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="js/lingulablive.js"></script>

</head>
<body>

<div id="content">
<img src="_img/header_bg.jpg" width="950" height="195" />
<div id="form">

<?php if(!isset($_SESSION['username']) || $_SESSION['username']=="" || !$linguLabLive->checkLogin ()): ?>
	<h1 class="headline">Login</h1>
	
	<p style="color:red;"><?php if($_GET['action']!="logout") echo $linguLabLive->resultMessage; ?></p>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="loginForm">	
	<label for="username">Benutzername:</label><br/>
    <input name="username" type="text" id="username" class="txt" value="" />
    <br/><label for="pass">Passwort:</label><br/>
    <input name="pass" type="password" id="pass" class="txt" />
    <br/>
    <input type="submit" id="loginButton" value="Login"/>
    </form>
    
<?php else: ?>
	
	<form action="" method="post" onsubmit="return false;">
		
		<h1 class="headline">Text-Eingabe</h1>
		
		<p style="color:red;margin-bottom:25px;"><?php echo $linguLabLive->loginData; ?> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=logout" title="Logout">Logout</a></p>

		<p class="bodytext">
	    Bitte wählen Sie die Textgattung mit Hilfe des Dropdown-Menüs aus.<br />
	    <?php echo $linguLabLive->getConfiguration(); ?>
	    </p>
	    <p class="trenner"></p>
	    <p class="bodytext" id="keywordwrapper" style="border: 1px solid #622181; padding: 20px; margin: 0 20px 25px 20px;">
	    <b>Suchmaschinenrelevanz</b><br />
	    <br />
	    Geben Sie maximal drei Keywords ein. Die Suchmaschinenrelevanz des eingegebenen
	    Textes wird auf Grundlage dieser Keywords ausgewertet.<br />
	    <br />
	    1.    <input name="keyword1" type="text" id="keyword1" class="txt" />
	    2.    <input name="keyword2" type="text" id="keyword2" class="txt" />
	    3.    <input name="keyword3" type="text" id="keyword3" class="txt" />
	    </p>
	    <p class="trenner"></p>


	    <div class="bodytext_Result" style="border: 1px solid #622181; padding: 20px 20px 35px 20px; margin: 0 20px 25px 20px;">
			<div class="loading_icon"></div>
		    <b>Ergebnis</b><br />
			<br/>
            
			<div class="done">
			     Text bisher nicht &uuml;berpr&uuml;ft.
			</div>
            <div id="getContentButton" style="display: none;">
            <input type="button" name="refreshText" id="refreshText" value="Text aktualisieren" />
            </div>
	    </div>

	    <p class="trenner"></p>

	    <label for="headline">Headline:</label><br/>
	    <input name="headline" type="text" id="headline" class="txt" />
	    <br/><label for="subline">Subline:</label><br/>
	    <input name="subline" type="text" id="subline" class="txt" />
	    <br/>Teaser:<br/>
	    <input name="teaser" type="text" id="teaser" class="txt" />
		<br/><br/>

		 <textarea id="editor" name="editor"></textarea>
		<br/><br/>
		<input type="submit" id="submit" value="Submit"/>
		<p class="trenner"></p>
	</form>
<?php endif; ?>

</div>
</div>
</body>
</html>
