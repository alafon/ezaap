<?php

class ezsfServiceUserHandler extends ezsfService
{
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
