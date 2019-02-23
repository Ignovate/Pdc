<?php
/**
 * Created by PhpStorm.
 * User: prabugoodhope
 * Date: 25/09/18
 * Time: 9:50 AM
 */

class Dever_Sales_Model_Cron
{
    public function generateOrderReport()
    {
        $debug = true;
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getModel('core/resource');

        $connection = $resource->getConnection('default_setup');

        $filename = 'exportorders' . date('Y-m-dH:i:s') . '.csv';
        $file = Mage::getBaseDir('var') . DS . 'order' . DS . 'reports' . DS . 'exports.csv';

        $fp = fopen($file, 'w+');
        $csvHeader = array(
            "ordernumber",
            "pharmacyname",
            "firstname",
            "lastname",
            "approved_by",
            "status",
            "purchased_date",
            "processed_date",
            "delivered_date",
            "orderedqty",
            "invoicedqty",
            "shippedqty",
            "canceledqty",
            "total"
        );

        $sql = "
        select 
t1.increment_id as ordernumber,
_t3.name as pharmacyname,
t2.value as firstname,
_t2.value as lastname,
_t3.name as approved_by,
t1.status as status,
t1.created_at as purchased_date,
t1.updated_at as processed_date,
t1.updated_at as delivered_date,
SUM(_t1.qty_ordered) as orderedqty,
SUM(_t1.qty_invoiced) as invoicedqty,
SUM(_t1.qty_shipped) as shippedqty,
SUM(_t1.qty_canceled) as canceledqty,
t1.base_grand_total as total
from
sales_flat_order `t1`
left join sales_flat_order_item `_t1` on t1.entity_id = _t1.order_id
left join customer_entity_varchar `t2` on t1.customer_id = t2.entity_id and t2.attribute_id = 5
left join customer_entity_varchar `_t2` on t1.customer_id = t2.entity_id and t2.attribute_id = 5
left join customer_entity_int `t3` on t1.customer_id = t3.entity_id and t3.attribute_id = 139
left join sales_flat_order_item `t4` on t1.entity_id = t4.order_id
inner join retailstores `_t3` on t3.value = _t3.id
where 1=1
group by _t1.order_id
";
        $rows = $connection->fetchAll($sql);

        fputcsv($fp, $csvHeader, ",");
        foreach ($rows as $row) {
            fputcsv($fp, array(
                $row['ordernumber'],
                $row['pharmacyname'],
                $row['firstname'],
                $row['lastname'],
                $row['approved_by'],
                $row['status'],
                $row['purchased_date'],
                $row['processed_date'],
                $row['delivered_date'],
                $row['ordered_qty'],
                $row['invoiced_qty'],
                $row['shippedqty'],
                $row['canceledqty'],
                $row['total']
            ), ",");
        }

        fclose($fp);
    }

}