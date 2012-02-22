<?php

/**
 *
 * Convenience class to get configuration parameters for a give service
 *
 * @property string $Handler
 * @property string $Name
 * @property string $Server
 * @property string $AvailableMethods
 * @property string $AlwaysAddToken
 *
 */
class ezaapServiceConfiguration
{
    /**
     * The configuration filename where the services are defined
     */
    const CONFIG_FILE = 'ezaapservice.ini';

    /**
     *
     * The eZINI instance used to access configuration values
     *
     * @var eZINI
     */
    private $ezinifile;

    /**
     *
     * All the settings for the current service (as an associative)
     *
     * @var array
     */
    private $serviceBlockSettings;

    /**
     *
     * @var string
     */
    private $serviceName;

    /**
     *
     * Constructor
     *
     * @param ezaapService $service The service concerned by this configuration handler
     */
    public function __construct( ezaapService $service )
    {
        $this->serviceName = $service->getServiceName();
        $this->loadIniFile();
    }

    /**
     *
     * @param string $parameterKey
     * @param string $method
     * @return mixed
     */
    public function getParameter( $parameterKey, $method = null )
    {
        if( !is_null($method) )
        {
            $retVal = $this->getParameterByMethod( $parameterKey, $method );
        }
        else
        {
            $retVal = $this->serviceBlockSettings[$parameterKey];
        }
        return $retVal;
    }

    /**
     *
     * Returns true of $parameterKey is set for the given method
     *
     * @param string $parameterKey
     * @param string $method
     * @return boolean
     */
    public function isSetForMethod( $parameterKey, $method )
    {
        return array_key_exists( $method, $this->serviceBlockSettings[$parameterKey] );
    }

    /**
     *
     * Magic function to access configuration value which are the same for all
     * the methods
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if( in_array($name, array('Handler','Name','Server','AvailableMethods','AlwaysAddToken')) )
        {
            return $this->getParameter($name);
        }
        else
        {
            throw new Exception( 'Trying to access $name in ' . __CLASS__ );
        }
    }

    /**
     *
     * Convenience method to get the settings block name where the configuration
     * is located for a single service method
     *
     * @return string
     */
    private function serviceBlockName()
    {
        return "{$this->serviceName}Settings";
    }

    /**
     *
     * Returns the $parameterKey value for the given method
     *
     * @param string $method
     * @param string $parameterKey
     * @return mixed
     */
    private function getParameterByMethod( $parameterKey, $method )
    {
        if( !$this->isSetForMethod( $parameterKey, $method ) )
        {
            eZDebug::writeError( "Parameter $parameterKey is not set for method $method (service {$this->serviceName})", __CLASS__ );
            return null;
        }
        return $this->serviceBlockSettings[$parameterKey][$method];
    }

    /**
     * Load the eZINI file
     * @todo test if the file has been successfully loaded or not (ie: does the
     *       file exist ?)
     */
    private function loadIniFile()
    {
        $this->ezinifile = eZINI::instance( self::CONFIG_FILE );
        $this->serviceBlockSettings = $this->ezinifile->BlockValues[$this->serviceBlockName()];
    }
}

?>
