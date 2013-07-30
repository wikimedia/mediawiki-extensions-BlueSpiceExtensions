<?php
    session_start();
	require_once('lingulablive.func.php');
	$linguLabLive = new linguLabLiveWebservice();
	$linguLabLive->GetUpdatedText();
?>
