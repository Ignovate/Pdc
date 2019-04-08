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
$query = $db->query("select t1.increment_id as ordernumber, t1.customer_email as email, t1.customer_firstname as firstname, t1.customer_lastname as lastname, t1.created_at as purchased_date, t1.updated_at as processed_date, t1.updated_at as delivered_date, t1.status as status, t2.sku, t2.name, t2.price, t2.qty_ordered as ordered_qty, t2.qty_invoiced as invoiced_qty, t2.qty_shipped as shipped_qty, t2.qty_canceled as canceled_qty from sales_flat_order `t1` left join sales_flat_order_item `t2` on t1.entity_id = t2.order_id where 1=1 order by t1.entity_id DESC");

if($query->num_rows > 0){
    $delimiter = ",";
    $filename = "OrderItemReport_" . date('Y-m-d') . ".csv";
    
    //create a file pointer
    $f = fopen('php://memory', 'w');
    
    //set column headers
     $fields = array('ordernumber', 'sku', 'invoiced_qty', 'shipped_qty', 'canceled_qty','status','ordered_qty','email', 'firstname', 'lastname','name', 'price', 'purchased_date', 'processed_date', 'delivered_date' );
    fputcsv($f, $fields, $delimiter);
    
    //output each row of the data, format line as csv and write to file pointer
    while($row = $query->fetch_assoc()){
        //$status = ($row['status'] == '1')?'Active':'Inactive';
        $lineData = array($row['ordernumber'], $row['sku'],  $row['invoiced_qty'], $row['shipped_qty'], $row['canceled_qty'], $row['status'], $row['ordered_qty'], $row['email'], $row['firstname'], $row['lastname'], $row['name'], $row['price'], $row['purchased_date'], $row['processed_date'], $row['delivered_date']);
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