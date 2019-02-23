<?php

class Dever_Api2_Model_Router extends Mage_Api2_Model_Router
{

    /**
     * Route the Request, the only responsibility of the class
     * Find route that match current URL, set parameters of the route to Request object
     * Rewrited for add checking api_version from added to api_url
     *
     * @param Mage_Api2_Model_Request $request
     * @return Mage_Api2_Model_Request
     * @throws Mage_Api2_Exception
     */
    public function route(Mage_Api2_Model_Request $request)
    {
        $isMatched = false;

        //START CHANGES
        //checking version in url , saving to param and removing from url if exists.
        $version = '';
        preg_match("/(v\d)/", $request->getPathInfo(), $parts);
        if (!empty($parts)) {
            $request->setPathInfo(preg_replace("/(\/v\d)/", "", $request->getPathInfo()));
            $request->setRequestUri(preg_replace("/(\/v\d)/", "", $request->getRequestUri()));

            preg_match("/\d+/", $parts[0], $parts);
            $version = $parts[0];
            $request->setParam('url_version', $version);
        }
        //END CHANGES
        /** @var $route Mage_Api2_Model_Route_Interface */
        foreach ($this->getRoutes() as $route) {
            if ($route->getDefault('version') != $version) {
                continue;
            }
            if ($params = $route->match($request)) {
                $request->setParams($params);
                $isMatched = true;
                break;
            }
        }
        if (!$isMatched) {
            throw new Mage_Api2_Exception('Request does not match any route.', Mage_Api2_Model_Server::HTTP_NOT_FOUND);
        }
        if (!$request->getResourceType() || !$request->getModel()) {
            throw new Mage_Api2_Exception('Matched resource is not properly set.',
                Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        return $request;
    }

}
