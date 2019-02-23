<?php

class Dever_Api2_Model_Auth_Adapter_Key
    extends Mage_Api2_Model_Auth_Adapter_Abstract
{
    /**
     * Process request and figure out an API user type and its identifier
     *
     * Returns stdClass object with two properties: type and id
     *
     * @param Mage_Api2_Model_Request $request
     * @return stdClass
     */
    public function getUserParams(Mage_Api2_Model_Request $request)
    {
//        $header = $request->getHeader('Authorization');
//
//        list($authName, $authValue) = array_filter(array_map('trim', explode(' ', $header)));
//        $authValue = $authValue;

        $authValue = '336fac317cb3f7e1446ac01ebb4e0fcf';

        /** @var Mage_Oauth_Model_Token $token */
        $token = Mage::getModel('oauth/token')->load($authValue, 'token');
        if (!$token->getId()) {
            throw new Mage_Api2_Exception('Wrong auth token.', Mage_Api2_Model_Server::HTTP_UNAUTHORIZED);
        }

        if ($token->getAdminId()) {
            return (object) array('type' => 'admin', 'id' => $token->getAdminId());
        }

        throw new Mage_Api2_Exception('Token not assigned to admin user', Mage_Api2_Model_Server::HTTP_UNAUTHORIZED);
    }

    /**
     * Check if request contains authentication info for adapter
     *
     * @param Mage_Api2_Model_Request $request
     * @return boolean
     */
    public function isApplicableToRequest(Mage_Api2_Model_Request $request)
    {
        $headerValue = $request->getHeader('Authorization');

        return $headerValue && 'agentx' === strtolower(substr($headerValue, 0, 8));
    }
}
