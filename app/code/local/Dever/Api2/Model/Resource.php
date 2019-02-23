<?php

class Dever_Api2_Model_Resource extends Mage_Api2_Model_Resource
{
    const DEFAULT_WEBSITE = 2;

    const DEFAULT_STORE = 2;

    const DEFAULT_CURRENCY  = 'INR';

    const ACTION_TYPE_VIRTUAL  = 'virtual';

    protected $_storeId = null;

    protected $_lang = null;

    protected $_currencyCode = null;

    /**
     * Dispatch
     * To implement the functionality, you must create a method in the parent one.
     *
     * Action type is defined in api2.xml in the routes section and depends on entity (single object)
     * or collection (several objects).
     *
     * HTTP_MULTI_STATUS is used for several status codes in the response
     */
    public function dispatch()
    {
        $debug = true;
        switch ($this->getActionType() . $this->getOperation()) {
            /* Create */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_CREATE:
                // Creation of objects is possible only when working with collection
                $this->_critical(self::RESOURCE_METHOD_NOT_IMPLEMENTED);
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_CREATE:
                // If no of the methods(multi or single) is implemented, request body is not checked
                if (!$this->_checkMethodExist('_create') && !$this->_checkMethodExist('_multiCreate')) {
                    $this->_critical(self::RESOURCE_METHOD_NOT_IMPLEMENTED);
                }
                // If one of the methods(multi or single) is implemented, request body must not be empty
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                // The create action has the dynamic type which depends on data in the request body
                if ($this->getRequest()->isAssocArrayInRequestBody()) {
                    $this->_errorIfMethodNotExist('_create');
                    $result = $this->_create($requestData);
                    if ($result) {
                        $this->_render($result);
                    } else {
                        $this->_render($this->getResponse()->getMessages());
                    }
                } else {
                    $this->_errorIfMethodNotExist('_multiCreate');
                    $this->_multiCreate($requestData);
                    $this->getResponse()->setHttpResponseCode(Mage_Api2_Model_Server::HTTP_MULTI_STATUS);
                    $this->_render($this->getResponse()->getMessages());
                }
                break;
            /* Retrieve */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_RETRIEVE:
                $this->_errorIfMethodNotExist('_retrieve');
                $retrievedData = $this->_retrieve();
                $this->_render($retrievedData);
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_RETRIEVE:
                $this->_errorIfMethodNotExist('_retrieveCollection');
                $retrievedData = $this->_retrieveCollection();
                $this->_render($retrievedData);
                break;
            /* Update */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_UPDATE:
                $this->_errorIfMethodNotExist('_update');
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                $result = $this->_update($requestData);
                if (is_array($result)) {
                    $this->_render($result);
                } else {
                    $this->_render($this->getResponse()->getMessages());
                }
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_UPDATE:
                $this->_errorIfMethodNotExist('_multiUpdate');
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                $result = $this->_multiUpdate($requestData);
                if (is_array($result)) {
                    $this->_render($result);
                } else {
                    $this->_render($this->getResponse()->getMessages());
                }
                break;
            /* Delete */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_DELETE:
                $this->_errorIfMethodNotExist('_delete');
                $result = $this->_delete();
                if (is_array($result)) {
                    $this->_render($result);
                } else {
                    $this->_render($this->getResponse()->getMessages());
                }
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_DELETE:
                $this->_errorIfMethodNotExist('_multiDelete');
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                $this->_multiDelete($requestData);
                $this->getResponse()->setHttpResponseCode(Mage_Api2_Model_Server::HTTP_MULTI_STATUS);
                break;
            case self::ACTION_TYPE_VIRTUAL . self::OPERATION_CREATE:
                $this->_errorIfMethodNotExist('_create');
                // If one of the methods(multi or single) is implemented, request body must not be empty
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                $result = $this->_create($requestData);
                $this->_render($result);
                break;
            case self::ACTION_TYPE_VIRTUAL . self::OPERATION_DELETE:
                $this->_errorIfMethodNotExist('_delete');
                $result = $this->_delete();
                if (is_array($result)) {
                    $this->_render($result);
                } else {
                    $this->_render($this->getResponse()->getMessages());
                }
                break;
            default:
                $this->_critical(self::RESOURCE_METHOD_NOT_IMPLEMENTED);
                break;
        }

        $this->_postDispatch();
    }

    /**
     * Get store ID from country value in request. If nothing is sent in
     * request use default value
     */
    public function getStoreId()
    {
        return Mage::app()->getStore()->getId();
    }

    public function getCurrencyCode()
    {
        if (is_null($this->_currencyCode)) {
            if (($currencyCode = $this->getRequest()->getParam('currency')) && preg_match('/^[a-zA-Z]*$/', $currencyCode)) {
                $this->_currencyCode = $currencyCode;
            } else {
                $this->_currencyCode = self::DEFAULT_CURRENCY;
            }
        }

        return $this->_currencyCode;
    }

    protected function _preDispatch()
    {
        // Set current store based on given country param
        $requestStore = $this->getRequest()->getParam('country', false);
        if (!$requestStore) {
            Mage::throwException('Country resource is required');
        }
        // Search for store ID in list of website stores
        $storeIds = array_flip(Mage::app()->getWebsite()->getStoreCodes());
        if (!isset($storeIds[$requestStore])) {
            Mage::throwException('Such country resource does not exist');
        }
        Mage::app()->setCurrentStore($requestStore);

        // Set current currency based on given currency code
        Mage::app()->getStore()->setCurrentCurrencyCode(
            $this->getCurrencyCode(), false
        );
        // Set global locale based on given lang
        $allowedLang = Mage::app()->getLocale()->getLanguages();
        if (isset($allowedLang[$this->getLang()])) {
            Mage::app()->getLocale()->setLocale($allowedLang[$this->getLang()]['locale']);
        } else {
            Mage::app()->getLocale()->setLocale($this->getLang());
        }
        Mage::app()->getLocale()->setCurrentLanguage($this->getLang());
        Mage::app()->getTranslator()->init('frontend');

        return $this;
    }

    protected function _postDispatch()
    {
        return $this;
    }

    public function validateUserGroup()
    {
        if ($this->getApiUser()->getUserId()) {
            // Load customer by current user ID
            $customer = Mage::getModel('customer/customer')
                ->load($this->getApiUser()->getUserId());
            if (!$customer->getId()) {
                Mage::throwException("Customer no longer exists in the system");
            }
            // TODO Add smart ACL based on customer group
            if ($customer->getGroupId() == 5) {
                Mage::throwException(
                    "Customer is in black list. Action is not allowed"
                );
            }
        }
    }

    /**
     * Get Requested Store Param Id
     *
     * @return int
     * @throws Exception
     * @throws Mage_Api2_Exception
     */
    protected function _getStoreId()
    {
        $store = $this->getRequest()->getParam('store');
        try {
            if ($this->getUserType() == Mage_Api2_Model_Auth_User_Admin::USER_TYPE) {
                // Set Current store as admin to force eav
                Mage::app()->setCurrentStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
                // Get requested store param data
                $store = Mage::app()->getStore($store);
            }
        } catch (Mage_Core_Model_Store_Exception $e) {
            // store does not exist
            $this->_critical('Requested store is invalid', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        return $store->getId();
    }

    /**
     * Pre Processing request data
     *
     * @param $data
     * @param Varien_Object $obj
     * @return array
     */
    protected function _preProcessData($data, Varien_Object $obj)
    {
        /** @var Dever_Api2_Helper_Data $helper */
        $helper = Mage::helper('dever_api2');

        $helper->setSource($data);

        return $helper->filterSource($obj);
    }

    protected function getAdapter()
    {
        $readAdapter = Mage::getSingleton('core/resource')
            ->getConnection('core_read');

        return $readAdapter;

    }
}
