<?php

$module = $Params['Module'];

$serviceName = $Params['Service'];
$methodToCall = $Params['Method'];

$service = ezsfService::get( $serviceName );
if( !$service->availableThroughServiceModule() )
{
    // à améliorer
    eZLog::write( "Tentative d'accès au service $serviceName par {$_SERVER["REMOTE_ADDR"]}", "sfconnect_security.log" );
    return $module->handleError( eZError::KERNEL_MODULE_VIEW_NOT_FOUND, 'kernel' );
}


if( is_null( $methodToCall ) )
    return $module->handleError( eZError::KERNEL_MODULE_VIEW_NOT_FOUND, 'kernel' );

$params = array( 'get_parameters' => $_GET );

$service->$methodToCall( $params );

$Result['pagelayout'] = false;
$Result['content'] = $service->getResponseContent();