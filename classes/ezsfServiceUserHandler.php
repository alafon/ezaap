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
     * For security reason
     * This service can not be called using /sf/service/User/Edit
     *
     * @return type
     */
    public function availableThroughServiceModule()
    {
        return false;
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
        $this->addTokenToRequest();
    }
    public function preBannerResponse()
    {
        $this->responseContent = $this->response->getContent();
    }
}
