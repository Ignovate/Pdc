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
$query = $db->query("SELECT id as id, order_id as orderid, customer_email as customeremail, items as items, firstname as firstname, lastname as lastname, street as street, country_id as country, city as city, postcode as pincode, telephone as phone, status as status, message as comment,skippedSku, import_dtime as sheetimportedtime, timestamp as orderimportedtime from custom_bulk_order");
if($query->num_rows > 0){
    $delimiter = ",";
    $filename = "OrderUploadLog_" . date('Y-m-d') . ".csv";
    
    //create a file pointer
    $f = fopen('php://memory', 'w');
    
    //set column headers
    $fields = array('id', 'orderid', 'customeremail', 'items', 'firstname', 'lastname', 'street', 'country', 'city', 'pncode', 'phone', 'status', 'comment','skippedSku', 'sheetimportedtime','orderimportedtime');
    fputcsv($f, $fields, $delimiter);
    
    //output each row of the data, format line as csv and write to file pointer
    while($row = $query->fetch_assoc()){
        //$status = ($row['status'] == '1')?'Active':'Inactive';
        $lineData = array($row['id'], $row['orderid'], $row['customeremail'], $row['items'], $row['firstname'], $row['lastname'], $row['street'], $row['country'], $row['city'], $row['pincode'], $row['phone'], $row['status'], $row['comment'],$row['skippedSku'], $row['sheetimportedtime'], $row['orderimportedtime']);
        fputcsv($f, $lineData, $delimiter);
    }
    
    //move back to beginning of file
    fseek($f, 0);
    
    //set headers to download file rather than displayed
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    
    //output all remaining data on a file pointer
    fpassthru($f);
}else{
	$filename = "OrderUploadLog_" . date('Y-m-d') . ".csv";
	$f = fopen('php://memory', 'w');
	$fields = array('id', 'orderid', 'customeremail', 'items', 'firstname', 'lastname', 'street', 'country', 'city', 'pncode', 'phone', 'status', 'comment','skippedSku', 'sheetimportedtime','orderimportedtime');
    fputcsv($f, $fields, $delimiter);
	while($row = $query->fetch_assoc()){
        //$status = ($row['status'] == '1')?'Active':'Inactive';
        $lineData = array($row['id'], $row['orderid'], $row['customeremail'], $row['items'], $row['firstname'], $row['lastname'], $row['street'], $row['country'], $row['city'], $row['pincode'], $row['phone'], $row['status'], $row['comment'],$row['skippedSku'], $row['sheetimportedtime'], $row['orderimportedtime']);
        fputcsv($f, $lineData, $delimiter);
    }
	fseek($f, 0);
	header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    
    //output all remaining data on a file pointer
    fpassthru($f)
}
exit;
}
?>