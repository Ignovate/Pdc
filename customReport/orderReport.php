<?php
//include database configuration file
$dbHost     = 'pdcdrugstoreSQL';
$dbUsername = 'root';
$dbPassword = 'iwRCKICX4olDry4yQclM1x';
$dbName     = 'pdc';

//Create connection and select DB
$db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

if($db->connect_error){
    die("Unable to connect database: " . $db->connect_error);
}else{

//get records from database
$query = $db->query("select t1.increment_id as ordernumber, _t3.name as pharmacyname, t2.value as firstname, _t2.value as lastname, _t3.name as approved_by, t1.status, t1.created_at as purchased_date, t1.updated_at as processed_date, t1.updated_at as delivered_date, _t1.qty_ordered as orderedqty, _t1.qty_invoiced as invoicedqty, _t1.qty_shipped as shippedqty, _t1.qty_canceled as canceledqty, t1.base_grand_total as total from sales_flat_order `t1` left join sales_flat_order_item `_t1` on t1.entity_id = _t1.order_id left join customer_entity_varchar `t2` on t1.customer_id = t2.entity_id and t2.attribute_id = 5 left join customer_entity_varchar `_t2` on t1.customer_id = t2.entity_id and t2.attribute_id = 5 left join customer_entity_int `t3` on t1.customer_id = t3.entity_id and t3.attribute_id = 139 left join sales_flat_order_item `t4` on t1.entity_id = t4.order_id inner join retailstores `_t3` on t3.value = _t3.id where 1=1 group by t1.entity_id order by t1.entity_id DESC");

if($query->num_rows > 0){
    $delimiter = ",";
    $filename = "OrderReport_" . date('Y-m-d') . ".csv";
    
    //create a file pointer
    $f = fopen('php://memory', 'w');
    
    //set column headers
    $fields = array('ordernumber', 'pharmacyname', 'firstname', 'lastname', 'approved_by', 'status', 'purchased_date', 'processed_date', 'delivered_date', 'orderedqty', 'invoicedqty', 'shippedqty', 'canceledqty', 'total');
    fputcsv($f, $fields, $delimiter);
    
    //output each row of the data, format line as csv and write to file pointer
    while($row = $query->fetch_assoc()){
        //$status = ($row['status'] == '1')?'Active':'Inactive';
        $lineData = array($row['ordernumber'], $row['pharmacyname'], $row['firstname'], $row['lastname'], $row['approved_by'], $row['status'], $row['purchased_date'], $row['processed_date'], $row['delivered_date'], $row['orderedqty'], $row['invoicedqty'], $row['shippedqty'], $row['canceledqty'], $row['total']);
        fputcsv($f, $lineData, $delimiter);
    }
    
    //move back to beginning of file
    fseek($f, 0);
    
    //set headers to download file rather than displayed
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    
    //output all remaining data on a file pointer
    fpassthru($f);
}
exit;
}
?>