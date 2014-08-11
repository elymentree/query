<?php/* #  AUTHOR       : ELIZAR M. FLORES #  DESCRIPTION  : PDO BASED DYNAMIC SQL QUERY FUNCTIONS #  VERSION      : 2.0 #  USAGE        : REFER TO DOCUMENTTATION AT www.elizarflores.com/contribution or download at github.com/elymentree/query #  The MIT License (MIT) Copyright (c) 2014 Elizar Flores  	 *//*----------------------------------------------------------------------------------------------------------------------*/ defined('DB_SERVER') ? null : define("DB_SERVER", DB_HOST); defined('DB_USER')   ? null : define("DB_USER"  , DB_USER); defined('DB_PASS')   ? null : define("DB_PASS"  , DB_PASSWORD); defined('DB_NAME')   ? null : define("DB_NAME"  , DB_NAME);/*----------------------------------------------------------------------------------------------------------------------*/ class QUERY{    private $dbh;	    private $_conn;     private $_columns = array();       protected $_result = array();     private $_table_raw;     private $_table;    private $_join;    private $_key;    private $_cols;    private $_group;    private $_order;    private $_limit;    private $_sub;    private $_format;    private $_query_string = false;    public  $fetch =  array();    private $_modifier = '37124RF70R35';    public function __construct($assets = array()){		 $this->_conn = $this->dbh = new PDO('mysql:host=' . DB_SERVER . ';dbname='. DB_NAME, DB_USER, DB_PASS);		 $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	         if(!empty($assets)):		  $this->_table_raw  = array_key_exists('TABLE' ,$assets)   ? $assets['TABLE']   : false;                  $this->_join       = array_key_exists('JOIN', $assets)    ? $assets['JOIN']    : false; 		  $this->_cols       = array_key_exists('COLS'  ,$assets)   ? $assets['COLS']    : false; 		  $this->_key        = array_key_exists('KEY'   ,$assets)   ? $assets['KEY']     : false; 		  $this->_sub        = array_key_exists('SUB_KEY' ,$assets) ? $assets['SUB_KEY'] : false;                   $this->_group      = array_key_exists('GROUP' ,$assets)   ? $assets['GROUP']   : false;                   $this->_order      = array_key_exists('ORDER' ,$assets)   ? $assets['ORDER']   : false; 		  $this->_limit      = array_key_exists('LIMIT', $assets)   ? $assets['LIMIT']   : false; 		  $this->_format     = array_key_exists('FORMAT', $assets)  ? $assets['FORMAT']  : false; 		  $this->PROCESS_TABLE_NAME();                  ($this->_table && ($this->_key || $this->_join)) ? $this->PROCESS_TABLE(): $this->SET_COLUMNS();                     endif;	 }        private function PROCESS_TABLE_NAME($arrs=false){            $table_array = !$arrs ? $this->_table_raw : $arrs;             $_table = explode('::', $table_array);            $_return      =  isset($_table[1])&&$_table[1] ? $_table[0].' '.$_table[1] : $_table[0];            $this->_table =  $_table[0];            return $_return;        }          private function PROCESS_JOIN_CLAUSE($arrs = array()){         $str = '';            if(!isset($arrs[0])):             $join_type  = isset($arrs['TYPE']) ? $arrs['TYPE'] . ' JOIN ' : ' INNER JOIN ';             $join_table = $this->PROCESS_TABLE_NAME($arrs['TABLE']);             $join_on    = $arrs['ON'];             $str        = ' '.$join_type .  $join_table .' ON '. $join_on;          else:             foreach($arrs as $arr):             $join_type  = isset($arr['TYPE']) ? $arr['TYPE'] . ' JOIN ' : ' INNER JOIN ';             $join_table = $this->PROCESS_TABLE_NAME($arr['TABLE']);             $join_on    = $arr['ON'];             $str        .= ' '.$join_type .  $join_table .' ON '. $join_on;         endforeach;             endif;               return $str;         }        private function PROCESS_GROUP_BY_CLAUSE($clause){            $str = ' GROUP BY ' . $clause . ' ';            return $str;        } 	private function PROCESS_COLUMNS_ARRAY($arrs = array()){             $str = '';            foreach($arrs as $key => $arr):                 $str .= !is_numeric($key) ? 'CONCAT('.$arr.') AS ' . $key . ',' : $arr .',';             endforeach; 	            return rtrim($str,',');	}	private function PROCESS_KEYS_ARRAY($arrs = array())	{          $str = ' WHERE ';	 if(!is_array($arrs) && $arrs):	    $str .= '1'; 	 else:             foreach($arrs as $key => $arr):                     $temp_key = explode('::', $key);                     $key_name = $temp_key[0];                     $process  = isset($temp_key[1])&&$temp_key[1]?$temp_key[1]:' = ';                     $str .= $key_name .' '.$process .' :' . $key_name . ' AND ';             endforeach; 	 	 endif;         return rtrim($str,'AND ');	}        private function CLEANUP_KEYS($arrs=array()){            $clean = array();            $keys  = $arrs;            foreach($keys as $k => $v):                $_index =explode('::',$k);                 $clean[$_index[0]] = $v;             endforeach;            return $clean;        }	private function PROCESS_ORDER()	{                $str=' ORDER BY ';                $_order = explode('::',  $this->_order);                $_by = isset($_order[1])?$_order[1]:'ASC';                  $str .= $_order[0].' '.$_by.' ' ; 		return $str; 	   		}	private function PROCESS_LIMIT_CLAUSE(){		$str=' LIMIT ';		$str .= $this->_limit ? $this->_limit : ''; 		return $str; 	   		}	private function SET_COLUMNS(){	 $this->_cols = is_array($this->_cols) ? $this->_cols : ($this->_cols ? explode(',',$this->_cols) : false); 	 if($this->_cols):	    foreach($this->_cols as $key => $col):		   $this->_columns[trim($key)] = trim($col);	            endforeach;	  else:                 $prepared = $this->_conn->prepare("SHOW COLUMNS FROM " .  $this->_table);		                 $prepared->execute();		while($row = $prepared->fetch()):                  $this->_columns[$row['Field']] = '';                endwhile;	  endif;	}   private function PROCESS_TABLE(){          $this->SET_COLUMNS();	  $join    = $this->_join                           ? $this->PROCESS_JOIN_CLAUSE($this->_join)     :'';            $columns = $this->_cols                           ? $this->PROCESS_COLUMNS_ARRAY($this->_columns):'*';           $keys    = $this->_key                            ? $this->PROCESS_KEYS_ARRAY($this->_key)       :'';            $sub     = $this->_sub && is_string($this->_sub)  ? ' ' . $this->_sub . ' '                      :'';  	  $group   = $this->_group                          ? $this->PROCESS_GROUP_BY_CLAUSE($this->_group):'';            $order   = $this->_order                          ? $this->PROCESS_ORDER()                       :'';  	  $limit   = $this->_limit                          ? $this->PROCESS_LIMIT_CLAUSE()                :'';  	  $query    = 'SELECT ' . $columns . ' FROM '. $this->PROCESS_TABLE_NAME() . $join . $keys . $sub . $group . $order . $limit;          $this->_query_string = $query;	  $prepared = $this->_conn->prepare($query);	  ((!is_array($this->_key) && $this->_key)  || ((!is_array($this->_key) && $this->_key) && $this->_join) || ($this->_join && !(is_array($this->_key) && $this->_key))  ) ? $prepared->execute() : $prepared->execute($this->CLEANUP_KEYS($this->_key));          $result   =  $prepared->fetchAll(PDO::FETCH_ASSOC); 	  foreach($result as $key => $values):		  foreach($values as $keyVal => $value):	             if(is_array($this->_format) && in_array($keyVal,array_keys($this->_format))):                             $value = eval('return(' . $this->_format[$keyVal] .  ');');                     endif;                     $this->_result[$key][$keyVal] = trim($value); 		endforeach; 	  endforeach;          $this->fetch = isset($this->_result[0])? (object) $this->_result[0]:false;         return $prepared;   }   private function EXECUTE_QUERY(){       $prepared = $this->_conn->prepare($this->_query_string);         ((!is_array($this->_key) && $this->_key)  || ((!is_array($this->_key) && $this->_key) && $this->_join) || ($this->_join && !(is_array($this->_key) && $this->_key))  ) ? $prepared->execute() : $prepared->execute($this->CLEANUP_KEYS($this->_key));       return $prepared;     }   private function PROCESS_DATA_SET($ARRS=array()){	   $str = '';	   if(!empty($ARRS)):	       foreach($ARRS as $key => $arr):			 $str .= str_replace($this->_modifier,'',$key) . '=:'. $key . ',';               endforeach;              endif;	   return rtrim($str,',');   }  		   private function INSERT_QUERY($ARRS = array()){	$data   = $this->PROCESS_DATA_SET($ARRS);		$query  = 'INSERT INTO ' . $this->_table . ' SET ' . $data; 		$this->_query_string = $query;	$prepared   = $this->_conn->prepare($query);        $result     = $prepared->execute($ARRS);        return $result;    }    private function UPDATE_QUERY($ARRS = array()){	 $alterARRS = array();	 foreach($ARRS as $key => $val):			$alterARRS[$key.$this->_modifier] = $val;         endforeach;	$data = $this->PROCESS_DATA_SET($alterARRS);	$keys = $this->_key ? $this->PROCESS_KEYS_ARRAY($this->_key):'';	$bindARRs = array_merge($alterARRS,$this->_key);	$query = 'UPDATE ' . $this->_table . ' SET ' . $data . $keys;	$this->_query_string = $query;	$prepared = $this->_conn->prepare($query);	$result = $prepared->execute($bindARRs);	return $result;     }     private function DELETE_QUERY(){            $result = false;            $keys = $this->_key ? ( is_array($this->_key) ? $this->PROCESS_KEYS_ARRAY($this->_key) : false )    : false;            if($keys):                $query = 'DELETE FROM ' . $this->_table . $keys;                $prepared = $this->_conn->prepare($query);                $result = $prepared->execute($this->_key);		$this->_query_string = $query;            endif;           return $result;        }	private function RUN_QUERY($clause,$params){		$prepared = $this->_conn->prepare($clause);		$prepared->execute($params);		return $prepared; 	}             private function INSERT_MULTIPLE_QUERY($ARRS = array()){           $raw_cols = '(';           foreach($ARRS[0] as $key1 => $value):               $raw_cols .= $key1.',';            endforeach;           $final_cols = rtrim($raw_cols,',') . ')';           $ctr1=0;  $raw_vals='';           foreach($ARRS as $ARR_VALUE):               $raw_vals .= '(';               foreach($ARR_VALUE as $key => $value): $raw_vals .= ':'.$key.$this->_modifier.'_'.$ctr1.','; endforeach;               $raw_vals  = rtrim($raw_vals,',');               $raw_vals .= '),';               $ctr1++;           endforeach;           $final_vals = rtrim($raw_vals,',');           $ctr2 = 0; $param = array();           foreach($ARRS as $ARR_PARAM):               foreach($ARR_PARAM as $key_param => $value_param):$param[$key_param.$this->_modifier.'_'.$ctr2] = $value_param; endforeach;               $ctr2++;           endforeach;           $clause = 'INSERT INTO ' . $this->_table . $final_cols . ' VALUES ' . $final_vals;           $result = $this->RUN_QUERY($clause, $param);           return $result ? true:false;        }# PUBLIC FUNCTIONS            public function fetch($key,$place=0){return !empty($this->_result)?$this->_result[$place][$key]:false;}        public function result(){return $this->EXECUTE_QUERY();}        public function fetchRow($place=0){ return !empty($this->_result)?$this->_result[$place]:false;}        public function fetchAll(){return !empty($this->_result)?$this->_result:false;}		public function lastId(){ return $this->_conn->lastInsertId();}	public function save($data){ return  $this->_key && is_array($this->_key) && !empty($this->_result) ?  $this->UPDATE_QUERY($data) : $this->INSERT_QUERY($data);}        public function save_many($multi_data = array()){ return $this->INSERT_MULTIPLE_QUERY($multi_data);}        public function delete(){ return $this->DELETE_QUERY();}        public function numRows(){ return count($this->_result);}       	public function run($clause,$params=array()){ return $this->RUN_QUERY($clause,$params);}		public function showLastQuery(){ return $this->_query_string ? $this->_query_string : false;}        public function pre($array){    echo'<pre>'; print_r($array); echo'</pre>'; }} // END CLASS?>