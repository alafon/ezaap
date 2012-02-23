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

    public function preEditResponse()
    {
        $this->responseContent = $this->response->getContent();
    }

    public function preBannerResponse()
    {
        $this->responseContent = $this->response->getContent();
    }

    public function postStorePublicSessionResponse()
    {
        $this->responseContent = $this->response->getContent();
    }
}
