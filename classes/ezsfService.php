<?php

/**
 * @property Buzz\Browser $buzz the buzz instance
 */
abstract class ezsfService
{
    const CONFIG_FILE = 'sfservice.ini';

    private static $services = array();
    private $configuration;

    /**
     *
     * The Buzz browser
     *
     * @var Buzz\Browser
     */
    protected $buzz;


    /**
     *
     * The Buzz response instance
     *
     * @var Buzz\Message\Response
     */
    protected $response;

    /**
     *
     * The buzz request instance (can be Request or FormRequest)
     *
     * @var Buzz\Message\Request
     */
    protected $request;

    /**
     *
     * The name of the current method
     *
     * @var string
     */
    protected $currentMethod;

    public function __construct( $serviceName )
    {
        $this->serviceName = $serviceName;

        $ini = eZINI::instance( self::CONFIG_FILE );
        $this->configuration = $ini->BlockValues["{$serviceName}Settings"];

        $this->buzz = new Buzz\Browser();
    }

    /**
     *
     * Gère l'appel à un service/method
     *
     * @param string $method
     * @param mixed $arguments
     */
    public function __call( $method, $arguments )
    {
        $this->currentMethod = $method;

        $uri = $this->configuration['URI'][$method];
        $server = $this->configuration['Server'];

        $this->response = new Buzz\Message\Response();

        switch( $this->configuration['RequestTypes'][$method] )
        {
            case 'get':
                $this->request = new Buzz\Message\Request();
                break;
            case 'ajax':
                // todo enrichissement du post pour l'ajax
            case 'post':
                $this->request = new Buzz\Message\FormRequest();
                break;
        }

        // déclenche les actions spécifiques à ce service / methode
        // en termes de construction de la requete
        $this->populateRequest();

        $this->request->setHost( $server );
        $this->request->setResource( $uri );

        $client = new Buzz\Client\Curl();
        $client->send( $this->request, $this->response );

        // déclenche les actions spécifiques à ce service / methode
        // en termes de gestion de la réponse
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


    private function populateRequest()
    {
        $methodNameSuffix = ucfirst( $this->currentMethod ) . "Request";
        $preMethodName = "pre{$methodNameSuffix}";
        $postMethodName = "post{$methodNameSuffix}";

        // pre 'request' trigger
        if( method_exists( $this, $preMethodName ) )
        {
            $this->$preMethodName();
        }

        // post 'request' trigger
        if( method_exists( $this, $postMethodName ) )
        {
            $this->$postMethodName();
        }
    }

    private function handleResponse()
    {
        $methodNameSuffix = ucfirst( $this->currentMethod ) . "Response";
        $preMethodName = "pre{$methodNameSuffix}";
        $postMethodName = "post{$methodNameSuffix}";

        // 'pre' response trigger
        if( method_exists( $this, $preMethodName ) )
        {
            $this->$preMethodName();
        }

        /* @var $response Buzz\Message\Response */
        $response = $this->response;

        $returnCode = $response->getStatusCode();
        $this->responseContent = $response->getContent();

        // 'post' response trigger
        if( method_exists( $this, $postMethodName ) )
        {
            $this->$postMethodName();
        }
    }

    /**
     *
     * Get the response retrieved by buzz
     *
     * @return type
     */
    public function getResponseContent()
    {
        return $this->responseContent;
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