<?php

$Module = array( 'name' => 'Backend Service Router' );

$ViewList = array();
$ViewList['service'] = array(
    'script' => 'service.php',
    'params' => array( 'Service', 'Method' ),
    'functions' => array( 'call' )
    );

$FunctionList = array();
$FunctionList['call'] = array();


?>
