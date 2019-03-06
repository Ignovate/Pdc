<?php
/**
 * Created by PhpStorm.
 * User: thillai.rajendran
 * Date: 6/22/16
 * Time: 11:24 AM
 */
class Dever_Import_Model_Import extends Mage_Core_Model_Abstract
{
    public function saveProductOptions($data)
    {
        /** @var Dever_Import_Helper_Import $helper */
        $helper = Mage::helper('dever_import/import');

        foreach ($data as $code => $value)
        {
            if (empty($value) || $value == '') {
                continue;
            }
            //Check attribute type
            $type = $helper->checkDefaultAttributeType($code);
            if ($type) {
                $checkAttr = $helper->attributeValueExists($code, $value);
                if ($checkAttr) {
                    continue;
                }
                // Create new option
                $helper->saveAttributeOptions(
                    $code,
                    $value
                );
            } else {
                continue;
            }
        }
    }

    public function prepareDataForImport($data)
    {
        /** @var Dever_Import_Helper_Import $helper */
        $helper = Mage::helper('dever_import/import');
        foreach ($data as $code => $value)
        {
            if (empty($value) || $value == '') {
                continue;
            }

            $product = Mage::getModel('catalog/product');
            //Check attribute type
            $type = $helper->checkDefaultAttributeType($code);
            if ($type) {

                $optionId = $helper->attributeValueExists($code, $value);
                if ($optionId) {
                    $data[$code] = $optionId;
                }

            } else {
                continue;
            }
            unset($product);
        }

        return $data;
    }

    /**
     * Save New Product
     * Product type config , simple
     * @param $index
     */
    public function saveProduct($index, $mediaDir)
    {
        try {
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product');
            $productId = $product->getIdBySku($index['sku']);
            if (empty($productId) || $product == '') {
                $product->load(null);
                $product->setSku($index['sku'])
                    ->setTypeId('simple')
                    ->setWeight(1)
                    ->setWebsiteIds(array(2))
                    ->setAttributeSetId(4)
                    ->setCreatedAt(strtotime('now'))
                    ->setTaxClassId(4)
                    ->setVisibility($index['visibility'])
                    ->setName($index['name'])
                    ->setDescription($index['name'])
                    ->setShortDescription($index['name'])
                    ->setStatus($index['status']);

                if ($index['category']) {
                    //Get Category Id by name
                    $category = Mage::getModel('catalog/category')
                        ->getCollection()
                        ->addAttributeToFilter('name', $index['category'])
                        ->getFirstItem();
                    $categoryIds = explode('/', $category->getId());
                    $product->setCategoryIds($categoryIds);
                    unset($index['category']);
                }

                if ($index['images']) {
                    $this->addImages($product, $index['images'], $mediaDir);
                    unset($index['images']);
                }

                //Price fields
                $product->setPrice(1);
                $product->setCost(1);
                $product->addData($index);
                if ($product->save()) {
                    //Stock Data
                    $_product = Mage::getModel('catalog/product')->load($product->getId());
                    $_product->setStockData(
                        array (
                            'use_config_manage_stock' => 0,
                            'manage_stock' => 1,
                            'min_sale_qty' => 1,
                            'is_in_stock' => 1,
                            'qty' => 100000
                        )
                    );
                    $_product->save();
                    echo "Product Save - {$_product->getSku()} Done \n";
                    unset($product, $_product);
                }
            }

        } catch (Exception $e) {

            echo (string)$e->getMessage();
        }
    }

    public function addImages($product, $images, $dir)
    {
        $imgArr = explode(',', $images);
        foreach ($imgArr as $key => $imgUrl)
        {
            $image_type = substr(strrchr($imgUrl, "."), 1);
            $filename = md5($imgUrl) . '.' . $image_type;
            //Simple hack for image
            $newimage = explode('?', $filename);
            $filepath = Mage::getBaseDir('media') . DS . 'import' . DS . $newimage[0];
            file_put_contents($filepath, file_get_contents(trim($imgUrl)));
            $mediaAttribute = array('thumbnail', 'small_image', 'image');
            $obj = $product->addImageToMediaGallery($filepath, $mediaAttribute, false, false);
            if (!$obj) {
                return false;
            }
        }
    }

    public function addImagesBySku($product, $sku, $dir)
    {
        //Jpg image
        $imgPath = Mage::getBaseDir('media') . DS . 'import' . DS . $dir . DS . $sku . '.jpg';
        $mediaAttribute = array('thumbnail', 'small_image', 'image');
        $obj = $product->addImageToMediaGallery($imgPath, $mediaAttribute, false, false);
        if ($obj) {
            echo "......Product Image uploaded {$imgPath}\n";
        }
    }
}
