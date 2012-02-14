<?php

/**
 * @property Buzz\Browser $buzz the buzz instance
 */
abstract class ezsfService
{
    const CONFIG_FILE = 'sfservice.ini';

    private static $services = array();

    protected $buzz;

    protected $configuration;

    public function __construct( $serviceName )
    {
        $this->serviceName = $serviceName;

        $ini = eZINI::instance( self::CONFIG_FILE );
        $this->configuration = $ini->BlockValues["{$serviceName}Settings"];

        $this->buzz = new Buzz\Browser();
    }

    public function __call( $method, $arguments )
    {
        $url = $this->buildURL( $this->configuration['Server'],
                                $this->configuration['URI'][$method]
                );

        $this->populateRequest();

        switch( $this->configuration['RequestTypes'][$method] )
        {
            case 'get':
                $this->buzz->get( $url );
                break;
            case 'ajax':
                // todo enrichissement du post pour l'ajax
            case 'post':
                $this->buzz->post( $url );
                break;
        }

        $this->handleResponse();
    }

    /**
     *
     * Build the URL
     *
     * @todo Validates URL with regexp
     *
     * @param string $serveur
     * @param string $uri
     * @param integer $port
     * @param string $protocol
     *
     * @return string
     */
    public function buildURL( $serveur, $uri, $port = 80, $protocol = 'http'  )
    {
        return "{$protocol}://{$serveur}:{$port}{$uri}";
    }

    /**
     * Has to be reimpl in the service handler
     */
    abstract function populateRequest();

    /**
     * Has to be reimpl in the service handler
     */
    abstract function handleResponse();

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