<?php 
require_once( 'instantavatar.php' );

header( 'content-type: image/png' );
$ia = new InstantAvatar( 'Comfortaa-Regular.ttf', 18, 40, 40, 2, 'glass.png' );
$ia->generateRandom( 'ia' );
$ia->passThru();

?>