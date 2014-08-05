<?php
# INCLUDE THE CLASS FILE
include('query.php');

# MAKE AN INSTANCE OF THE CLASS AND SET THE PARAMETERS (SEE DOCUMENTATION FOR MORE INFO)
# THIS INSTANCE FETCHES ALL ROWS THAT HAVE A 'MALE' GENDER ON THE TABLE
$test = new QUERY(array('TABLE'=>'person','KEY'=>array('gender'=>'MALE')));

# FETCH ALL THE RESULTS
$result = $test->fetchAll();

# SHOW THE RESULTS
echo'<pre>';
print_r($result);
echo'</pre>';

echo'<hr />';

# CREATING AN INSTANCE POINTING TO ONE RESULT
$test2 = new QUERY(array('TABLE'=>'person','KEY'=>array('id'=>'1')));

# SHOWING THE RESULT
echo '<strong>' . $test2->fetch('name') . '</strong> resides at <strong>' . $test2->fetch('address') . '</strong>';

# SAVING DATA USING THE CLASS
$data  = array('name'=>'Elizar','address'=>'1 City Drive','birthday'=>'2000-10-10','gender'=>'male');
$test3 = new QUERY(array('TABLE'=>'person'));
$test3->save($data); 
 
# WILL SAVE THE CONTENTS OF ARRAY <$data> TO TABLE `person` 
 
# FOR MORE EXAMPLES AND OPTIONS ON THIS CLASS PLEASE SEE THE DOCUMENTATION WRITTEN AT THE BEGINNING OF THE FILE
# ELIZAR M. FLORES 
?>