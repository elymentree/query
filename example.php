<?php include_once('includes/init.php'); ?>
<h2>Basic Example</h2>
<hr />
<?php
#initiate query
$query1 = new QUERY(array('TABLE'=>'employees','KEY'=>1));
#to fetch a single row
$row = $query1->fetchRow();
#show rows
$query1->pre($row);
#fetch all results
$results = $query1->fetchAll();
#loop through the results
foreach($results as $row):
    echo $row['lastName'].'<br />';
endforeach;
?>
<h2>Join example</h2>
<hr />
<?php
# initialize the join object
$join = array(array('TABLE'=>'employees::e','ON'=>'c.salesRepEmployeeNumber = e.employeeNumber'),
              array('TABLE'=>'offices::o','ON'=>'o.officeCode = e.officeCode')); 
# apply a formatting to a column
$format = array('customerName'=>'strtoupper($value)');
#initiate the query
$query = new QUERY(array('TABLE'=>'customers::c','JOIN'=>$join,'KEY'=>array('customerNumber'=>103),'COLS'=>array('c.customerName','e.firstName','o.city'),'FORMAT'=>$format));
# fetch a row 
echo 'city: ' .$query->fetch->city .'<br />';
#fetch all results
$result = $query->fetchAll();
# show results 
$query->pre($result);


