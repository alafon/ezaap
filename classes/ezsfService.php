<?php

/**
 *
 * @todo Gérer les timeouts
 *
 */
abstract class ezsfService
{
    const CONFIG_FILE = 'ezsfservice.ini';
    const COOKIE_TOKEN_NAME = '_token';
    const ROUTE_PREFIX_GET_PARAMETER = 'route_prefix';
    const DEFAULT_TIMEOUT = 10;
    const LOG_FILE = "ezsf";

    protected $configuration;

    /**
     *
     * The name of the current service
     *
     * @var string
     */
    private $serviceName;

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
     * Arguments to be used by the triggers
     *
     * @var array
     */
    protected $requestArguments;


    /**
     *
     * The response which will be made available outside this class (using
     * getResponseContent())
     *
     * @var string
     */
    protected $responseContent;

    /**
     *
     * Variable used to add a token
     *
     * @var string
     */
    protected $tokenToUse = null;

    /**
     *
     * Variable used to add a route prefix
     *
     * @var string
     */
    protected $routePrefix;

    /**
     *
     * @var Buzz\Client\Curl
     */
    protected $client;

    /**
     *
     * The name of the current method
     *
     * @var string
     */
    protected $currentMethod;

    /**
     *
     * @param string $serviceName
     * @param boolean $useCurrentUserToken
     */
    public function __construct( $serviceName, $useCurrentUserToken = false )
    {
        $this->serviceName = $serviceName;

        // todo handler de configuration par method
        $ini = eZINI::instance( self::CONFIG_FILE );
        $this->configuration = $ini->BlockValues["{$serviceName}Settings"];

        if( $useCurrentUserToken )
        {
            $this->tokenToUse = ezsfUser::getFromSessionObject()->token;
        }

        $this->client = new Buzz\Client\Curl();
        $this->client->setTimeout( self::DEFAULT_TIMEOUT );
    }

    private function availableMethods()
    {
        return $this->configuration['AvailableMethods'];
    }

    /**
     * A reimpl pour indiquer si le service symfony peut etre appelé par le
     * proxy eZ Publish /sf/service/<servicename>/<method>
     *
     * Doit retourner un booleen
     */
    abstract public function availableThroughServiceModule();

    /**
     *
     * Gère l'appel à un service/method
     *
     * @param string $method
     * @param mixed $arguments
     */
    public function __call( $method, $arguments = array() )
    {
        $this->requestArguments = $arguments[0];

        try
        {
            if( array_search( $method, $this->availableMethods()) === false )
            {
                // @todo page d'erreur eZ si debug désactivé
                throw new Exception( "Method {$method} non existante dans " . get_called_class() );
            }
            $this->currentMethod = $method;

            // est-ce que l'URI a appelé est configuré
            if( isset( $this->configuration['URI'][$method] ) )
            {
                $uri = $this->configuration['URI'][$method];
            }
            else
            {
                $uri = '/';
            }

            $server = $this->configuration['Server'];

            $this->response = new Buzz\Message\Response();
            // reinit responseContent in case that we call several methods on
            // a single service
            $this->responseContent = null;

            if( isset($this->configuration['RequestTypes'][$method] ) )
                $requestType = $this->configuration['RequestTypes'][$method];
            else
                $requestType = 'notconfigured';

            // predefines $this->request using configuration settings if possible
            switch( $requestType )
            {
                case 'ajax':
                    // todo enrichissement du post pour l'ajax
                case 'post':
                    $this->request = new Buzz\Message\FormRequest();
                    break;
                case 'get':
                default:
                    $this->request = new Buzz\Message\Request();
                    break;
            }
            $this->request->setHost( $server );
            $this->request->setResource( $uri );

            // déclenche les actions spécifiques à ce service / methode
            // en termes de construction de la requete
            $this->populateRequest();

            $this->client->send( $this->request, $this->response );

            // déclenche les actions spécifiques à ce service / methode
            // en termes de gestion de la réponse
            $this->handleResponse();
            $this->log();
            //var_dump( $this->request, $this->response );
        }
        catch( Exception $e )
        {
            // ici on ne gère que le code d'erreur timeout pour curl
            // les codes erreurs pour les autres clients seront
            // vraisemblablement différents
            if( $e->getCode() == CURLE_OPERATION_TIMEOUTED )
            {
                $this->responseContent = "Timeout";
            }
            else
            {
                eZDebug::writeDebug( "Catch exception : {$e->getMessage()}", __CLASS__ );
            }
        }
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

        // traitements génériques effectués sur toutes les requetes
        // ajoute le prefix si $this->routePrefix n'est pas null
        $this->addRoutePrefixToRequest();
        // ajout le token si fixé
        if( $this->tokenToUse )
        {
            $this->addTokenToRequest( $this->tokenToUse );
        }
        // ajoute le token en se basant sur la config si possible
        elseif( $this->configuration['AlwaysAddToken'] == 'true' )
        {
            $this->addTokenToRequest();
        }
        $this->addLocaleToRequest();

        // Add cookies stored in the User Session
        $userCookies = ezsfUser::instance()->cookies;
        if( !empty ( $userCookies ))
        {
            $cookiesHeader = array();
            foreach( $userCookies as $name => $value )
            {
                $cookiesHeader[] = "$name=$value";
            }
            $cookieString = "Cookie: " . implode( "; ", $cookiesHeader );
            $this->request->addHeader( $cookieString );
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

        // traitements génériques
        if( $this->response->getStatusCode() == 500 )
        {
            $this->responseContent = "Erreur 500 sur le backend";
        }

        // deal with response containing set-cookie header
        // store it in the eZ user session
        if( $this->response->getHeader( 'Set-Cookie' ))
        {
            $cookie = new \Buzz\Cookie\Cookie();
            $cookie->fromSetCookieHeader($this->response->getHeader( 'Set-Cookie' ) );
            ezsfUser::instance()->setCookie( $cookie->getName(), $cookie->getValue() );
        }

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
     * @param boolean $useCurrentToken
     * @return ezsfService
     */
    public static function get( $serviceName, $useCurrentToken = false )
    {
        // @todo lazy loading with static stuff
        return self::loadService( $serviceName, $useCurrentToken );

    }

    /**
     * Returns tthe service handler for the given $serviceName
     *
     * @param string $serviceName
     * @return sfService
     */
    private static function loadService( $serviceName, $useCurrentToken = false )
    {
        // for futur usage
        $handlerParams = array( $serviceName, $useCurrentToken );

        // get the handler using ezp api
        $iniBlockName = "{$serviceName}Settings";
        $optionArray = array( 'iniFile'      => self::CONFIG_FILE,
                              'iniSection'   => $iniBlockName,
                              'iniVariable'  => 'Handler',
                              'handlerParams'=> $handlerParams );

        $options = new ezpExtensionOptions( $optionArray );

        return eZExtension::getHandlerClass( $options );
    }

    protected function getResponseCode()
    {
        return $this->response->getStatusCode();
    }

    public function getJSONResponse()
    {
        return json_decode( $this->getResponseContent() );
    }

    protected function addLocaleToRequest()
    {
        $this->request->addHeader( "eZ-Locale: " . substr( eZLocale::currentLocaleCode(), 0, 2 ) );
    }

    protected function addTokenToRequest( $token = false )
    {
        if( $token === false )
        {
            // try to use a token already set
            if( !$this->tokenToUse )
            {
                $this->tokenToUse = ezsfUser::getFromSessionObject()->token;
            }
        }
        $cookie = new \Buzz\Cookie\Cookie();
        $cookie->setName( self::COOKIE_TOKEN_NAME );
        $cookie->setValue( $this->tokenToUse );
        $this->request->addHeader( $cookie->toCookieHeader() );
    }

    public function setRoutePrefix( $prefix )
    {
        $this->routePrefix = $prefix;
    }

    /**
     * Adds the route prefix used by the backend to generate urls
     * Set in GET parameters and headers (not used by the backend)
     */
    protected function addRoutePrefixToRequest()
    {
        if( !is_null($this->routePrefix) )
        {
            $this->addGetParameter( self::ROUTE_PREFIX_GET_PARAMETER, $this->routePrefix );
            $this->request->addHeader( "eZ-Route-Prexix: {$this->routePrefix}" );
        }
    }

    protected function addGetParameter( $key, $value )
    {
        $currentResource = $this->request->getResource();

        $parseURL = parse_url( $currentResource );
        $path = $parseURL['path'];
        $query = !isset( $parseURL['query'] ) ? "" : $parseURL['query'];
        $query .= "&{$key}={$value}";

        $newResource = "{$path}" . (strlen($query)?"?{$query}":"");
        $this->request->setResource( $newResource );
    }

    /**
     *
     * Transforms a Buzz\Message\Request into a Buzz\Message\FormRequest
     * and keeps host, resource and headers set in the Request
     *
     * @param \Buzz\Message\Request $request
     */
    protected static function transformToFormRequest( \Buzz\Message\Request &$request )
    {
        $newRequest = new Buzz\Message\FormRequest();
        $newRequest->setHost( $request->getHost() );
        $newRequest->setResource( $request->getResource() );
        $newRequest->setHeaders( $request->getHeaders() );
        $request = $newRequest;
    }

    /**
     *
     * Logs information about the request and the response
     *
     */
    protected function log()
    {
        $logFile = self::LOG_FILE . "_{$this->serviceName}.log";
        $message =  "\nREQ - Resource: {$this->request->getResource()} Method: {$this->request->getMethod()}";
        $message .= "\nREQ - Token: " . $this->tokenToUse;
        $message .= "\nRES - ContentLength: " . strlen($this->response->getContent()) . " Status Code: {$this->response->getStatusCode()}";
        eZLog::write( $message, $logFile );
    }
}

?>