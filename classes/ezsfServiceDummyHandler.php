<?php

class ezsfServiceDummyHandler extends ezsfService
{
    /**
     *
     * Returns an array containing the method provided by the service
     *
     * @todo load from settings
     *
     * @return array
     */
    public function availableMethods()
    {
        return array( 'Dummy' );
    }

    /**
     *
     * For security reason
     * This service can not be called using /sf/service/User/Edit
     *
     * @return type
     */
    public function availableThroughServiceModule()
    {
        return true;
    }

    /**
     * Pre trigger used to forward everything to the backend
     *
     * Adds the token to the request if [DummySettings].AlwaysAddToken=true
     */
    public function preDummyRequest()
    {
        // forwards SF URI and GET parameters being forwarded by the /sf/service
        // ezpublish module
        $resource = $this->requestArguments['sf_uri'];
        $getString = "";
        foreach( $this->requestArguments['get_parameters'] as $key => $value )
        {
            $getString .= "{$key}={$value}";
        }
        if(strlen($getString))
        {
            $resource .= "&$getString";
        }

        $this->request->setResource($resource);
        $this->setRoutePrefix( '/sf/service/Dummy/Dummy' );

        // forwards POST parameters if available
        if( $_POST )
        {
            self::transformToFormRequest( $this->request );
            $this->request->addFields( $_POST );
        }

        if( $this->configuration['AlwaysAddToken'] == 'true' )
        {
            $this->addTokenToRequest();
        }
    }

    public function postDummyResponse()
    {
        $this->responseContent = $this->response->getContent();
    }
}
