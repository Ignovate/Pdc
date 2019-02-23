<?php

class Dever_Api2_Model_Config extends Mage_Api2_Model_Config
{
    /**
     * Fetch all routes of the given api type from config files api2.xml
     *
     * @param string $apiType
     * @throws Mage_Api2_Exception
     * @return array
     */
    public function getRoutes($apiType)
    {
        /** @var $helper Mage_Api2_Helper_Data */
        $helper = Mage::helper('api2');
        if (!$helper->isApiTypeSupported($apiType)) {
            throw new Mage_Api2_Exception(sprintf('API type "%s" is not supported', $apiType),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        $routes = array();
        foreach ($this->getResources() as $resourceKey => $resource) {
            if (!$resource->routes) {
                continue;
            }

            /** @var $routes Varien_Simplexml_Element */
            foreach ($resource->routes->children() as $route) {
                $arguments = array(
                    Mage_Api2_Model_Route_Abstract::PARAM_ROUTE    => (string) $route->route,
                    Mage_Api2_Model_Route_Abstract::PARAM_DEFAULTS => array(
                        'model'       => (string) $resource->model,
                        'type'        => (string) $resourceKey,
                        'action_type' => (string) $route->action_type,
                        'version'     => (string) $route->version,
                    )
                );

                $routes[] = Mage::getModel('api2/route_' . $apiType, $arguments);
            }
        }
        return $routes;
    }
}
