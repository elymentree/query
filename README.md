query
=====
This script is written for the purposes of helping other programmers. 
This is OPEN SOURCE and you are free to use it anyway you like, a little acknowledgement on your footnote would do.
Config and usage is within the documentation of the script. Code your hearts out!

To run demo included on this file: 

1. Import the SQL file attached on the demo folder
2. Set the connection parameters on the QUERY class
3. Run the example on your web server

 Author: Elizar M. Fores @ http://www.elizarflores.com
 
<hr />

<div class='main-container'>
<h1>QUERY CLASS INITIALIZE</h1>

<div class='item-container'>
<h2>Declaring TABLES</h2>
<h3>TABLE <small>(<span>type</span>: STRING)</small></h3>
<p>The TABLE name. Set alias with <strong>::</strong> <span class="ver">(ver 2.0)</span> </p>
<pre>
'TABLE'=>'person'         <i>will point to person table</i>
'TABLE'=>'employee::emp'  <i><span class="ver">(ver 2.0)</span> will point to employee table with emp as an alias</i> 
</pre>
</div>

<div class='item-container'>
<h2>Defining KEYS</h2>
<h3>KEY <small>(<span>type</span>: MIXED)</small></h3>
<p>The identifiers to be used on the query. Multiple condition is set with multiple keys passed as an array, or set KEY to 1 to set WHERE to 1. <span class="ver">(ver 2.0)</span> Identify condition with <strong>::</strong></p>
<pre>
'KEY'=>1                                        <i>-will return | WHERE 1</i>
'KEY'=>array('user_id'=>1)                      <i>-will return | WHERE user_id = :user_id</i> 
'KEY'=>array('name'=>'elizar','gender'=>'male') <i>-will return | WHERE name=:name AND gender=:gender</i> 

<strong>New for ver 2.0</strong>

'KEY'=>array('first_name::LIKE'=>'elizar')                      <i>-will return | WHERE first_name LIKE :first_name</i> 
'KEY'=>array('email::LIKE'=>'%gmail%','office_code::<>'=>'123') <i>-will return | WHERE email LIKE :email AND office_code <> :office_code</i> 

<i>***Notice that the values are not binded right away to take advantage of PDO's BIND capabilites. The values are later binded upon execute when the values are fetched.</i>
</pre>
</div>


<div class='item-container'>
<h2>Adding SUB_KEYS</h2>
<h3>SUB_KEY <small>(<span>type</span>: STRING)</small></h3>
<p>Appends a SUB_KEY after the KEY clause</p>
<pre>
'SUB_KEY'=>' AND date_column IS NULL'
'SUB_KEY'=>' OR date_column NOT NULL'
</pre>
</div>

<div class='item-container'>
<h2>Specifying COLUMNS</h2>
<h3>COLS <small>(<span>type</span>: MIXED)</small></h3>
<p>The columns to be fetched. To CONCAT 2 or more columns, specify an associative array that will become the alias. ***You can also fetch the columns and do a formatting (see the MySQL )documentation for formatting</p>

<pre>
'COLS'=>'first_name'                                        <i>will return | SELECT first_name ...</i>
'COLS'=>'first_name,last_name'                              <i>will return | SELECT first_name, last_name ...</i>
'COLS'=>array('fist_name','last_name')                      <i>will return | SELECT first_name, last_name ...</i>
'COLS'=>array('name'=>'first_name,last_name')               <i>will return | SELECT CONCAT(first_name,last_name) AS name...</i>
'COLS'=>array('date'=>'DATE_FORMAT(date_added,"%M %d %Y")') <i>will return  a formatted date</i> 
</pre>
</div>

<div class='item-container'>
<h2>Structuring JOINS</h2>
<h3>JOIN <small>(<span>type</span>: ARRAY)</small></h3>
<p>Creates JOIN clauses. <span class="ver">(ver 2.0)</span>To use MULTIPLE JOINS specify a multi-dimensional array.</p>
<pre>
Array keys : 
      
    <i>                                          
    'TABLE' : String. The table name to be passed. <span class="ver">(ver 2.0)</span> Set 'TABLE'=>'person::p' to specify 'p' as alias for table
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
</div>



<div class='item-container'>
<h2>Adding GROUP BY</h2>
<h3>GROUP <small>(<span>type</span>: STRING)</small></h3>
<p>Appends a GROUP BY clause to the query.</p>
<pre>
'GROUP'=>'name'                <i>will return | GROUP BY name</i>
'GROUP'=>'person.name'         <i>will return | GROUP BY person.name</i>
'GROUP'=>'CONCAT(date,time)'   <i>will return | GROUP BY CONCAT(date,time)</i>
</pre>
</div>


<div class='item-container'>
<h2>Sorting query results by ORDER </h2>
<h3>ORDER <small>(<span>type</span>: STRING)</small></h3>
<p><span class="ver">(ver 2.0)</span> Appends an ORDER BY clause to the query.</p>
<pre>
array('ORDER'=>'id');                          <i>will return | ORDER BY id ASC</i>
array('ORDER'=>'id::ASC');                     <i>will return | ORDER BY id ASC</i>
array('ORDER'=>'date::DESC');                  <i>will return | ORDER BY date DESC</i>
array('ORDER'=>'CONCAT("date","time")::DESC'); <i>will return | ORDER BY CONCAT(date,time) DESC</i>
</pre>

<h3>ASC <small>(<span>type</span>: STRING)</small> DEPRECATED ON ver 2.0</h3>
<p>Appends an ORDER BY clause in ASC order.</p>
<pre>
'ASC'=>'user_id'             <i>will return | ORDER BY user_id ASC...</i>
'ASC'=>'CONCAT("date,time")' <i>will return | ORDER BY CONCAT(date,time) ASC...</i>
</pre>

<h3>DESC <small>(<span>type</span>: STRING)</small> DEPRECATED ON ver 2.0</h3>
<p>Appends an ORDER BY clause in DESC order.</p>
<pre>
'DESC'=>'user_id'             <i>will return | ORDER BY user_id DESC...</i>
'DESC'=>'CONCAT("date,time")' <i>will return | ORDER BY CONCAT(date,time) DESC...</i>
</pre>
</div>


<div class='item-container'>
<h2>Adding LIMIT clauses</h2>
<h3>LIMIT <small>(<span>type</span>: STRING)</small></h3>
<p>Creates the LIMIT clause of the query.</p>
<pre>
'LIMIT'=>'10'   <i>will return | LIMIT 10</i>
'LIMIT'=>'0,10' <i>will return | LIMIT 0,10</i> 
</pre>
</div>

<div class='item-container'>
<h2>Formatting the query results</h2>
<h3>FORMAT <small>(<span>type</span>: ARRAY)</small></h3>
<p>Passes a formatting function to be applied to the result of the query call.<br />The index must correspond to the columns of the table.Pass a < $value > variable as the replacement for the column variable to be processed.<br />The function call will be passed as a string in quotes ['']</p>
<pre>
'FORMAT'=>array('date_added'=>'date("M-d-Y, strtotime($value)")') <i>will format the date_added fetched result</i>        
'FORMAT'=>array('name'=>'strtolower($value)')                     <i>will format the name with strtolower function</i>
</pre>
</div>


<h1>QUERY CLASS function calls</h1>

<div class='item-container'>
<h2>Let person table: </h2>

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
</div>

<div class='item-container'>
<h2>PDO prepared</h2>
<h4><span class='func'>result()</span> function. <span class='ver'>(ver 2.0)</span> Call the PDO prepared variable.</h4>
<p>The program allows the class to fetch the prepared PDO data</p>
<pre>
$query = new QUERY(array('TABLE'=>'person::p','KEY'=>1));
$test  = $query->result();
while($row = $test->fetch()):
    echo $row['first_name'];
endwhile;   
</pre>
</div>

<div class='item-container'>
<h2>Fetching data</h2>
<h4><span class='func'>fetch(<span class='green'>string</span> <span class='blue'>$key</span>,[<span class='green'>int</span> <span class='blue'>$place</span>])</span> function. Used to fetch data from the table identified by the <span class='blue'>$key</span> which corresponds to the index of your table, <span class='green'>int</span> <span class='blue'>$place</span> defaults to 0 </h3>
<p>Not to be confused with the PDO method fetch() (but the name is not accidental. This is to give similar and extended functionality to the method)</p>

<pre>
$query = new QUERY(array('TABLE'=>'person::p','KEY'=>array('id'=>1)));

echo 'Hello, ' . $query->fetch('first_name); <i>prints : Hello, Peter</i>
</pre>

<h4><span style='color:#799954'>Object</span> <span class='func'>fetch</span>. An alternative way of fetching DATA <span class="ver">(ver 2.0)</span></h4>
<p>A single row result can be accessed as an object. If there are multiple rows on the result, the first row becomes an object</p>
<pre>
$query = new QUERY(array('TABLE'=>'person::p','KEY'=>array('id'=>1)));

echo 'Hello, ' . $query->fetch->first_name; <i>prints : Hello, Peter</i>
</pre>
</div>

<div class='item-container'>
<h2>Fetching a single row</h2>
<h4><span class='func'>fetchRow([<span class='green'>int</span> <span class='blue'>$key</span>])</span> function to fetch a single row from the results. <span class='green'>int</span> <span class='blue'>$row</span> defaults to 0 </h4>
<pre>
$query  = new QUERY(array('TABLE'=>'person::p','KEY'=>array('location'=>'CAN')));
$result = $query->fetchRow(2);

<i>will return: </i> 
-------------------------------------------------------------
| id  |     first_name      |       Gender     | location   |
|------------------------------------------------------------
| 3  |     John             |       male       | CAN        |
-------------------------------------------------------------
</pre>
</div>


<div class='item-container'>
<h2>Fetching ALL data</h2>
<h4><span class='func'>fetchAll()</span> function to fetch all results. (the PDO fetchAll function can be used if the PDO prepared was called via the <span class='func'>result()</span> function)</h4>
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
</div>


<div class='item-container'>
<h2>INSERTING and UPDATING DATA</h2>
<h4><span class='func'>save(<span class='green'>array</span> <span class='blue'>$data</span>)</span> function. Make sure that the array indexes corresponds to your table</h4>
<p>Upon initializing the QUERY class, the construct opens the row specified by the keys. If there is a result on the initialization, the <strong style="color:#793862 !important">save()</strong> function executes an UPDATE else, it executes an INSERT. </p>

<p>Examples</p>
<pre>
$query = new QUERY(array('TABLE'=>'person::p','KEY'=>array('first_name'=>'Peter')));
$data  = array('location'=>'USA');
$query->save($data); 

<i>Because the initialized constructs points to record with first_name = 'Peter', the <strong style="color:#793862 !important">save()</strong> function executes an UPDATE, thus changing the location column to 'USA'</i>
</pre>

<pre>
$query = new QUERY(array('TABLE'=>'person::p');
$data  = array('first_name'=>'Stephen','gender'=>'male','location'=>'USA');
$query->save($data);
 
<i>Because the initialized constructs does not point to a record, the <strong style="color:#793862 !important" >save()</strong> function executes an INSERT, thus creating a new record with first_name = 'Stephen','gender'=>'male','location'=>'USA'</i>
</pre>
</div>

<div class='item-container'>
<h2>INSERTING MULTIPLE DATA</h2>
<h4><span class='func'>save_many(<span class='green'>array</span> <span class='blue'>$data</span>)</span> function  <span class='ver'>(ver 2.0)</span>. Make sure that the array indexes corresponds to your table and is inserting in same column</h4>
<p>This function will run a single query with multiple insert, the <strong style="color:#793862 !important">save_many()</strong> function requires a multi-dimensional array with same structure per array </p>

<p>Examples</p>
<pre>
$query = new QUERY(array('TABLE'=>'person::p','KEY'=>1));
$multi_data  = array( 
                     array('first_name'=>'Levi','gender'=>'male','location'=>'USA'),
                     array('first_name'=>'Andrew','gender'=>'male','location'=>'CAN'),
                     array('first_name'=>'Mark','gender'=>'male','location'=>'USA'),
                     array('first_name'=>'Thomas','gender'=>'male','location'=>'CAN'),

$query->save($multi_data); 

<i>Notice that the indexes of the multi-dimensional array are the same</i>
</pre>
</div>

<div class='item-container'>
<h2>DELETING Records</h2>
<h4><span class='func'>delete()</span> function. <small style='color: #F00'>(Warning: Always execute delete queries with caution)</small></h3>
<p>Upon initializing the QUERY class, the construct opens the row specified by the keys. Calling the <strong style="color:#793862 !important">delete()</strong> function will delete the records. The <strong style="color:#793862 !important">delete()</strong> function follows key constraint rules</p>

<pre>
$query = new QUERY(array('TABLE'=>'person::p','KEY'=>array('first_name'=>'Peter')));
$query->delete(); 

<i>will delete the row with first_name = to 'Peter'</i>
</pre>
</div>


<div class='item-container'>
<h2>Getting the last insert id</h2>
<h4><span class='func'>lastId()</span> function. Gets last insert id</h4>
<pre>
$query = new QUERY(array('TABLE'=>'person::p');
$data  = array('first_name'=>'Stephen','gender'=>'male','location'=>'USA');
$query->save($data);
echo $query->lastId(); <i>prints the new record's new id</i>
</pre>
</div>

<div class='item-container'>
<h2>Getting total number of records</h2>
<h4><span class='func'>numRows()</span> function. Retrieves total number of rows</h3>
<pre>
$query = new QUERY(array('TABLE'=>'person::p','KEY'=>array('location'=>'CAN'));

echo $query->numRows(); <i>prints the total number of rows of the result</i>
</pre>
</div>


<div class='item-container'>
<h2>Execute own query clause</h2>
<h4><span class='func'>run(<span class='green'>string</span> <span class='blue'>$clause</span>,[<span class='green'>array</span> <span class='blue'>$param</span>])</span> function. </h3>
<p>Define and execute your own query clause. Bind variables by passing parameters of type array (optional)</p>

<pre>
$query  = new QUERY();
$clause = 'SELECT * FROM person';
$query->run($clause);
<i>runs and execute the query clause</i> 
</pre>

<pre>
$query  = new QUERY();
$clause = 'SELECT * FROM person WHERE id = :id';
$param  = array('id'=>1); 
$query->run($clause,$param);
<i>runs and execute the query clause and binds the <strong>$param</strong> array</i> 
</pre>



</div>



</div>

