<?php

class ezaapServiceOperators
{
    /**
     * Constructor
     *
     */
    function __construct()
    {
    }

    /**
     * Return an array with the template operator name.
     *
     * @return array
     */
    public function operatorList()
    {
        return array( 'ezaapservice' );
    }

    /**
     * Return true to tell the template engine that the parameter list exists per operator type,
     * this is needed for operator classes that have multiple operators.
     *
     * @return bool
     */
    function namedParameterPerOperator()
    {
        return true;
    }

    /**
     * Returns an array of named parameters, this allows for easier retrieval
     * of operator parameters. This also requires the function modify() has an extra
     * parameter called $namedParameters.
     *
     * @return array
     */
    public function namedParameterList()
    {
        return array( 'ezaapservice' => array( 'service_name' => array( 'type' => 'string',
                                                                       'required' => true,
                                                                       'default' => '' ),
                                              'service_method' => array( 'type' => 'integer',
                                                                         'required' => true,
                                                                         'default' => '' ),
                                              'use_current_token' => array( 'type' => 'boolean',
                                                                            'required' => false,
                                                                            'default' => false ),
                                              'service_parameters' => array( 'type' => 'integer',
                                                                             'required' => false,
                                                                             'default' => array() ) ) );

    }

    public function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch ( $operatorName )
        {
            case 'ezaapservice':
            {
                $method = $namedParameters['service_method'];
                $useCurrentToken = $namedParameters['use_current_token'];
                $service = ezaapService::get( $namedParameters['service_name'], $useCurrentToken );
                $service->$method( $namedParameters['service_parameters'] );
                $htmlResponse = $service->getResponseContent();
                $operatorValue = $htmlResponse;
            } break;
        }
    }
}

?>