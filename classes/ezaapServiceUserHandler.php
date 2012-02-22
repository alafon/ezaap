<?php

class ezaapServiceUserHandler extends ezaapService
{
    /**
     *
     * Available in module mode using /ezaap/service/User/xxx
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
        $this->setRoutePrefix( '/ezaap/service/User/Banner' );
        $this->addTokenToRequest();
    }
    public function preBannerResponse()
    {
        $this->responseContent = $this->response->getContent();
    }
}
