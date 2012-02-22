<?php

$Module = array( 'name' => 'Symfony Service Router' );

$ViewList = array();
$ViewList['service'] = array(
    'script' => 'service.php',
    'params' => array( 'Service', 'Method' ),
    'functions' => array( 'sfservice' )
    );

$FunctionList = array();
$FunctionList['sfservice'] = array();


?>
