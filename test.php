<?php
include_once('includes/init.php');


$query = new QUERY(array('TABLE'=>'employees::e','KEY'=>array('employeeNumber::LIKE'=>10 . '%','officeCode'=>1),'ORDER'=>'lastName::DESC'));


$temp = array('TABLE'=>'employees::e','KEY'=>array('employeeNumber::LIKE'=>1002));

$result = $query->fetchAll();

echo'<pre>';
print_r($result);
echo'</pre>'; // */

echo $query->showLastQuery();


//print_r($test);
/* $ctr = 0;
while($row = $test->fetch()):
    echo $row['employeeNumber'] . '<br />';
endwhile; // */



//echo 'here: ' . $query->fetch->employeeNumber; //.' - '.$query->fetch->lastName . ' - ' . $query->fetch->firstName;
//$temp = $query->fetch;

/*
echo'<pre>';
print_r($temp);
echo'</pre>'; // */

/*foreach($temp as $val):
    echo 'here: ' . $val['employeeNumber'] . '<br />';
    
 /*   echo'<pre>';
print_r($val);
echo'</pre>'; 
endforeach; // */
?>
<h2>QUERY CLASS INITIALIZE</h2>
<h3>Declaring TABLES</h3>
<h3>TABLE <small>(type: STRING)</small></h3>
<p>The TABLE name. (ver 2.0) Set alias with <strong>::</strong></p>
<pre>
'TABLE'=>'person'         <i>will point to person table</i>
'TABLE'=>'employee::emp'  <i>(ver 2.0) will point to employee table with emp as an alias</i> 
</pre>


<h3>Defining KEYS</h3>
<h3>KEY <small>(type: MIXED)</small></h3>
<p>The identifiers to be used on the query. Multiple condition is set with multiple keys passed as an array, or set KEY to 1 to set WHERE to 1. (ver 2.0) Identify condition with <strong>::</strong></p>
<pre>
'KEY'=>1                                        <i>-will return | WHERE 1</i>
'KEY'=>array('user_id'=>1)                      <i>-will return | WHERE user_id = :user_id</i> 
'KEY'=>array('name'=>'elizar','gender'=>'male') <i>-will return | WHERE name=:name AND gender=:gender</i> 

<strong>New for ver 2.0</strong>

'KEY'=>array('first_name::LIKE'=>'elizar')                      <i>-will return | WHERE first_name LIKE :first_name</i> 
'KEY'=>array('email::LIKE'=>'%gmail%','office_code::<>'=>'123') <i>-will return | WHERE email LIKE :email AND office_code <> :office_code</i> 

<i>***Notice that the values are not binded right away to take advantage of PDO's BIND capabilites. The values are later binded upon execute when the values are fetched.</i>

</pre>


<h3>Adding SUB_KEYS</h3>
<h3>SUB_KEY <small>(type: STRING)</small></h3>
<p>Appends a SUB_KEY after the KEY clause</p>
<pre>
'SUB_KEY'=>' AND date_column IS NULL'
'SUB_KEY'=>' OR date_column NOT NULL'
</pre>


<h3>Specifying COLUMNS</h3>
<h3>COLS <small>(type: MIXED)</small></h3>
<p>The columns to be fetched. To CONCAT 2 or more columns, specify an associative array that will become the alias. ***You can also fetch the columns and do a formatting (see the MySQL )documentation for formatting</p>

<pre>
'COLS'=>'first_name'                                        <i>will return | SELECT first_name ...</i>
'COLS'=>'first_name,last_name'                              <i>will return | SELECT first_name, last_name ...</i>
'COLS'=>array('fist_name','last_name')                      <i>will return | SELECT first_name, last_name ...</i>
'COLS'=>array('name'=>'first_name,last_name')               <i>will return | SELECT CONCAT(first_name,last_name) AS name...</i>
'COLS'=>array('date'=>'DATE_FORMAT(date_added,"%M %d %Y")') <i>will return  a formatted date</i> 
</pre>


<h3>Structuring JOINS</h3>
<h3>JOIN <small>(type: ARRAY)</small></h3>
<p>Creates JOIN clauses. (ver 2.0)To use MULTIPLE JOINS specify a multi-dimensional array.</p>
<pre>
Array keys : 
      
    <i>                                          
    'TABLE' : String. The table name to be passed. (ver 2.0) Set 'TABLE'=>'person::p' to specify 'p' as alias for table
    'ON'    : String. The ON clause for Join. 
    'TYPE'  : String. (Optional, INNER by default). The type of join 'INNER','LEFT','RIGHT','FULL'
    </i>
    
'JOIN'=>array('TABLE'=>'task::t','ON'=>'t.id = p.id')
'JOIN'=>array('TABLE'=>'task::t','TYPE'=>'LEFT','ON'=>'t.id = p.id')

<strong>Update for ver 2.0 (MULTIPLE JOINS)</strong>

$join = array(array('TABLE'=>'table1::a','ON'=>'a.code = x.code'),
              array('TABLE'=>'table2::aa','ON'=>'aa.code = a.code','TYPE'=>'RIGHT'),
              array('TABLE'=>'table2::aaa','ON'=>'aaa.code = aa.code','TYPE'=>'LEFT') 
              ); 


$query = new QUERY(array('TABLE'=>'employees::x','JOIN'=>$join));
<i>Would join all tables specified</i>
</pre>

<h3>Adding GROUP BY</h3>
<h3>GROUP <small>(type: STRING)</small></h3>
<p>Appends a GROUP BY clause to the query.</p>
<pre>
'GROUP'=>'name'                <i>will return | GROUP BY name</i>
'GROUP'=>'person.name'         <i>will return | GROUP BY person.name</i>
'GROUP'=>'CONCAT(date,time)'   <i>will return | GROUP BY CONCAT(date,time)</i>
</pre>

<h3>Sorting query results by ORDER </h3>
<h3>ORDER <small>(type: STRING)</small></h3>
<p>(ver 2.0) Appends an ORDER BY clause to the query.</p>
<pre>
array('ORDER'=>'id');                          <i>will return | ORDER BY id ASC</i>
array('ORDER'=>'id::ASC');                     <i>will return | ORDER BY id ASC</i>
array('ORDER'=>'date::DESC');                  <i>will return | ORDER BY date DESC</i>
array('ORDER'=>'CONCAT("date","time")::DESC'); <i>will return | ORDER BY CONCAT(date,time) DESC</i>
</pre>

<h3>ASC <small>(type: STRING)</small> DEPRECATED ON ver 2.0</h3>
<p>Appends an ORDER BY clause in ASC order.</p>
<pre>
'ASC'=>'user_id'             <i>will return | ORDER BY user_id ASC...</i>
'ASC'=>'CONCAT("date,time")' <i>will return | ORDER BY CONCAT(date,time) ASC...</i>
</pre>

<h3>DESC <small>(type: STRING)</small> DEPRECATED ON ver 2.0</h3>
<p>Appends an ORDER BY clause in DESC order.</p>
<pre>
'DESC'=>'user_id'             <i>will return | ORDER BY user_id DESC...</i>
'DESC'=>'CONCAT("date,time")' <i>will return | ORDER BY CONCAT(date,time) DESC...</i>
</pre>

<h3>Adding LIMIT clauses</h3>
<h3>LIMIT <small>(type: STRING)</small></h3>
<p>Creates the LIMIT clause of the query.</p>
<pre>
'LIMIT'=>'10'   <i>will return | LIMIT 10</i>
'LIMIT'=>'0,10' <i>will return | LIMIT 0,10</i> 
</pre>


<h3>Formatting the query results</h3>
<h3>FORMAT <small>(type: ARRAY)</small></h3>
<p>Passes a formatting function to be applied to the result of the query call.<br />The index must correspond to the columns of the table.Pass a < $value > variable as the replacement for the column variable to be processed.<br />The function call will be passed as a string in quotes ['']</p>
<pre>
'FORMAT'=>array('date_added'=>'date("M-d-Y, strtotime($value)")') <i>will format the date_added fetched result</i>        
'FORMAT'=>array('name'=>'strtolower($value)')                     <i>will format the name with strtolower function</i>
</pre>

<h2>QUERY CLASS function calls</h2>
<h3>Let person table: </h3>

<pre>
person table
-------------------------------------------------------------
| id  |     first_name      |       Gender     | location   |
|------------------------------------------------------------
| 1  |     Peter            |       male       | CAN        |  
| 2  |     Paul             |       male       | USA        |
| 3  |     John             |       male       | CAN        |
| 4  |     Mary             |       female     | CAN        |
-------------------------------------------------------------
</pre>

<h3>PDO prepared</h3>
<h3>(ver 2.0) Call the PDO prepared variable via result() function</h3>
<p>The program allows the class to fetch the prepared PDO data</p>
<pre>
$query = new QUERY(array('TABLE'=>'person::p','KEY'=>1));
$test  = $query->result();
while($row = $test->fetch()):
    echo $row['first_name'];
endwhile;   
</pre>


<h3>Fetching DATA</h3>
<h3>Use the fetch() function and specify the index</h3>
<pre>
$query = new QUERY(array('TABLE'=>'person::p','KEY'=>array('id'=>1)));

echo 'Hello, ' . $query->fetch('first_name); <i>prints : Hello, Peter</i>
</pre>

<h3>Alternative ways of fetching DATA (ver 2.0)</h3>
<h3>A single row result can be accessed as an object. If there are multiple rows on the result, the first row becomes an object</h3>
<pre>
$query = new QUERY(array('TABLE'=>'person::p','KEY'=>array('id'=>1)));

echo 'Hello, ' . $query->fetch->first_name; <i>prints : Hello, Peter</i>
</pre>


<h3>Fetching ALL DATA</h3>
<h3>Use the fetchAll() function to fetch all results. (the PDO fetchAll function can be used if the PDO prepared was called via the result() function)</h3>
<pre>
$query  = new QUERY(array('TABLE'=>'person::p','KEY'=>array('location'=>'CAN')));
$result = $query->fetchAll();

<i>will return: </i> 
-------------------------------------------------------------
| id  |     first_name      |       Gender     | location   |
|------------------------------------------------------------
| 1  |     Peter            |       male       | CAN        |  
| 3  |     John             |       male       | CAN        |
| 4  |     Mary             |       female     | CAN        |
-------------------------------------------------------------
</pre>

<h2>INSERTING and UPDATING DATA</h2>
<h3>Call save() function</h3>
<p>Upon initializing the QUERY class, the construct opens the row specified by the keys. If there is a result on the initialization, the <strong>save()</strong> function executes an UPDATE else, it executes an INSERT. Make sure that the array indexes corresponds to your table</p>

<p>Examples</p>
<pre>
$query = new QUERY(array('TABLE'=>'person::p','KEY'=>array('first_name'=>'Peter')));
$data  = array('location'=>'USA');
$query->save($data); 

<i>Because the initialized constructs points to record with first_name = 'Peter', the <strong>save()</strong> function executes an UPDATE, thus changing the location column to 'USA'</i>
</pre>

<pre>
$query = new QUERY(array('TABLE'=>'person::p');
$data  = array('first_name'=>'Stephen','gender'=>'male','location'=>'USA');
$query->save($data);
 
<i>Because the initialized constructs does not point to a record, the <strong>save()</strong> function executes an INSERT, thus creating a new record with first_name = 'Stephen','gender'=>'male','location'=>'USA'</i>
</pre>

<h2>DELETING Records</h2>
<h3>Call delete() function. <small>(WARNING!!! Always execute delete queries with caution)</small></h3>
<p>Upon initializing the QUERY class, the construct opens the row specified by the keys. Calling the <strong>delete()</strong> function will delete the records. The <strong>delete()</strong> function follows key constraint rules</p>

<pre>
$query = new QUERY(array('TABLE'=>'person::p','KEY'=>array('first_name'=>'Peter')));
$query->delete($data); 

<i>will delete the row with first_name = to 'Peter'</i>
</pre>


<h2>Getting the last insert id</h2>
<h3>Call lastId() function</h3>
<pre>
$query = new QUERY(array('TABLE'=>'person::p');
$data  = array('first_name'=>'Stephen','gender'=>'male','location'=>'USA');
$query->save($data);
echo $query->lastId(); <i>prints the new record's new id</i>
</pre>

<h2>Getting total number of records</h2>
<h3>Call numRows() function</h3>
<pre>
$query = new QUERY(array('TABLE'=>'person::p','KEY'=>array('location'=>'CAN'));

echo $query->numRows(); <i>prints the total number of rows of the result</i>
</pre>

