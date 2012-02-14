<?php

$Module = array( 'name' => 'Symfony Service Router' );

$ViewList = array();

// /sf/service/<ServiceName>/<Params>
$ViewList['service'] = array(
    'script' => 'service.php',
    'params' => array( 'Service' )
    );


?>
