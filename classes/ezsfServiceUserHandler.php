<?php

class ezsfServiceUserHandler extends ezsfService
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
        return array( 'Edit',
                      'Banner' );
    }

    /**
     *
     * Available in module mode using /sf/service/User/xxx
     *
     * @return type
     */
    public function availableThroughServiceModule()
    {
        return true;
    }

    public function preEditRequest()
    {
        $this->addTokenToRequest();
    }
    public function preEditResponse()
    {
        $this->responseContent = $this->response->getContent();
    }

    public function preBannerRequest()
    {
        $this->setRoutePrefix( '/sf/service/User/Banner' );
        $this->addTokenToRequest();
    }
    public function preBannerResponse()
    {
        $this->responseContent = $this->response->getContent();
    }
}
