<?php

class Dever_Api2_Model_Dispatcher extends Mage_Api2_Model_Dispatcher
{
    /**
     * Instantiate resource class, set parameters to the instance, run resource internal dispatch method
     * Rewrited for add checking api_version from added to api_url
     *
     * @param Mage_Api2_Model_Request $request
     * @param Mage_Api2_Model_Response $response
     * @return Mage_Api2_Model_Dispatcher
     * @throws Mage_Api2_Exception
     */
    public function dispatch(Mage_Api2_Model_Request $request, Mage_Api2_Model_Response $response)
    {
        if (!$request->getModel() || !$request->getApiType()) {
            throw new Mage_Api2_Exception(
                'Request does not contains all necessary data', Mage_Api2_Model_Server::HTTP_BAD_REQUEST
            );
        }
        //START CHANGES
        if ($version = $request->getParam('url_version')) {
            try {
                $model = self::loadResourceModel(
                    $request->getModel(),
                    $request->getApiType(),
                    $this->getApiUser()->getType(),
                    $version
                );
            } catch (Exception $e) {
                //if version model from url does not exists then load version from config.
                throw new Mage_Api2_Exception(
                    'Resource not found', Mage_Api2_Model_Server::HTTP_NOT_FOUND
                );
            }
        } else {
            $model = self::loadResourceModel(
                $request->getModel(),
                $request->getApiType(),
                $this->getApiUser()->getType(),
                $this->getVersion($request->getResourceType(), $request->getVersion())
            );
        }
        //END CHANGES

        $model->setRequest($request);
        $model->setResponse($response);
        $model->setApiUser($this->getApiUser());

        $model->dispatch();

        return $this;
    }
}
