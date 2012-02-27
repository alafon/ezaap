<?php

/* @var $module eZModule */
$module = $Params['Module'];

$serviceName = $Params['Service'];
$methodToCall = $Params['Method'];

// paramètres du module (Service et Method)
$namedParameters = $module->NamedParameters;

// faux view_parameters liés à l'URL générée coté backend
$otherParameters = array_slice( $module->ViewParameters, count($namedParameters) );
$sfURI = '/' . implode( '/', $otherParameters );

$service = ezaapService::get( $serviceName );
if( is_null( $service ) )
{
    eZLog::write( "Tentative d'accès au service $serviceName par {$_SERVER["REMOTE_ADDR"]}", "ezaap_security.log" );
    return $module->handleError( eZError::KERNEL_MODULE_VIEW_NOT_FOUND, 'kernel' );
}

if( !$service->availableThroughServiceModule() )
{
    // à améliorer
    eZLog::write( "Tentative d'accès au service $serviceName par {$_SERVER["REMOTE_ADDR"]}", "ezaap_security.log" );
    return $module->handleError( eZError::KERNEL_MODULE_VIEW_NOT_FOUND, 'kernel' );
}


if( is_null( $methodToCall ) )
    return $module->handleError( eZError::KERNEL_MODULE_VIEW_NOT_FOUND, 'kernel' );

$params = array( 'sf_uri' => $sfURI,
                 'get_parameters' => $_GET,
                 'post_request' => $_SERVER['REQUEST_METHOD'] == Buzz\Message\Request::METHOD_POST,
                 'referer' => (array_key_exists( 'HTTP_REFERER', $_SERVER )) ? $_SERVER['HTTP_REFERER'] : null );

if($params['post_request'])
{
    $params['post_parameters'] = $_POST;
}

$prefix = $module->Functions['service']['uri'];
$prefix .= "/{$serviceName}/{$methodToCall}";

$service->setRoutePrefix( $prefix );
$service->$methodToCall( $params );

if( $service->hasToBeRedirected() )
{
    $uri = $service->getRedirectURIAfterExecution();
    $module->redirectTo( $uri );
}

$Result['content'] = $service->getResponseContent();