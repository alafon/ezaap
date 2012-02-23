<?php

class ezaapServiceDummyHandler extends ezaapService
{
    /**
     *
     * For security reason
     * This service can not be called using /ezaap/service/User/Edit
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
        $this->setRoutePrefix( '/ezaap/service/Dummy/Dummy' );

        // forwards POST parameters if available
        if( $_POST )
        {
            self::transformToFormRequest( $this->request );
            $this->request->addFields( $_POST );
        }
    }

    public function postDummyResponse()
    {
        $this->responseContent = $this->response->getContent();
    }
}
