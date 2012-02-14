<?php

abstract class ezsfService
{
    const CONFIG_FILE = 'sfservice.ini';

    private static $services = array();

    public function __construct( $test )
    {
    }

    public function buildRequest()
    {
        // à partir de la conf
            // PROTOCOL
            // SERVEUR
            // PORT
            // URI
    }

    public function sendRequest()
    {
        // gestion du type de requete
            // GET
            // POST

        // Génération des headers + gestion des demandes AJAX
        // X-Requested-With: XMLHttpRequest
    }

    public function parseResponse()
    {
        // traitement des codes retour

        // contenu
            // contenu html
            // contenu json
            // contenu xml
            //
    }

    /**
     *
     * @param string $serviceName
     * @return ezsfService
     */
    public static function get( $serviceName )
    {
        // Lazy loading
        if( isset( self::$services[$serviceName] ) )
        {
            return self::$services[$serviceName];
        }
        else
        {
            $service = self::loadService( $serviceName );
            self::$services[$serviceName] = $service;
            return $service;
        }
    }

    /**
     * Returns tthe service handler for the given $serviceName
     *
     * @param string $serviceName
     * @return sfService
     */
    private static function loadService( $serviceName )
    {
        // for futur usage
        $handlerParams = array( $serviceName );

        // get the handler using ezp api
        $iniBlockName = "{$serviceName}Settings";
        $optionArray = array( 'iniFile'      => self::CONFIG_FILE,
                              'iniSection'   => $iniBlockName,
                              'iniVariable'  => 'Handler',
                              'handlerParams'=> $handlerParams );

        $options = new ezpExtensionOptions( $optionArray );

        return eZExtension::getHandlerClass( $options );
    }
}

?>