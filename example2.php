<?php include_once('includes/init.php'); ?>
<h2>Basic Example</h2>
<hr />
<?php
#initiate query
$query = new QUERY(array('TABLE'=>'person','KEY'=>1));
#to fetch a single row
$row = $query->fetchAll();
#show rows
$query->pre($row);
#fetch all results


$data = array(
        array('name'=>'Name1','address'=>'111 drive','birthday'=>'2012-09-01','gender'=>'FEMALE'),
        array('name'=>'Name2','address'=>'222 drive','birthday'=>'2012-08-01','gender'=>'MALE'),
        array('name'=>'Name3','address'=>'333 drive','birthday'=>'2012-07-01','gender'=>'FEMALE'),
        );

$single_data = array('name'=>'Name1','address'=>'111 drive','birthday'=>'2012-09-01','gender'=>'FEMALE');

echo'<hr />';
$result = $query->save_many($data);

echo $result;



?>


