<?php
/* test
 #  AUTHOR       : ELIZAR M. FLORES
 #  DESCRIPTION  : PDO BASED DYNAMIC SQL QUERY FUNCTIONS
 #  VERSION      : 2.1 (***NEW: CONNECTION OVERRIDE ON THE CLASS INSTANCE)
 #  USAGE        : REFER TO DOCUMENTATION AT www.elizarflores.com/contribution or download at github.com/elymentree/query
 #  The MIT License (MIT) Copyright (c) 2014 Elizar Flores
*/
/*----------------------------------------------------------------------------------------------------------------------*/
defined('QDB_HOST')   ? null : define("QDB_HOST"  , DB_HOST);
defined('QDB_USER')   ? null : define("QDB_USER"  , DB_USER);
defined('QDB_PASS')   ? null : define("QDB_PASS"  , DB_PASSWORD);
defined('QDB_NAME')   ? null : define("QDB_NAME"  , DB_NAME);
/*----------------------------------------------------------------------------------------------------------------------*/
class QUERY
{
    private $dbh;
    private $_conn;
    private $_columns  = array();
    protected $_result = array();
    private $_table_raw;
    private $_table;
    private $_join;
    private $_key;
    private $_cols;
    private $_group;
    private $_order;
    private $_limit;
    private $_sub;
    private $_tmpsubkey    = array(); // COLLECTED SUBKEY PARAMETERS FOR THE UPDATE AND THE DELETE
    private $_format;
    private $_query_string = false;
    private $_debug        = false;
    private $_showquery    = array('SELECT'=>false,'INSERT'=>false,'UPDATE'=>false,'DELETE'=>false);
    private $_auto_exec    = true; // boolean
    public  $fetch         =  array(); // CONTAINS THE OBJECT OF THE ROW RESULT

    # THE INITITAL QUERY STRING THAT NEEDS TO BE EXECUTED FIRST BEFORE THE CLASS CONSTRUCTS ALL THE RESULTS EG: SET SESSION group_concat_max_len = 1000000
    private $_init_exec = false;

    public function __construct($assets = array()){
        // CONNECTION OVERRIDE
        $CONN_OVERRIDE = isset($assets['CONNECTION'])&&is_array($assets['CONNECTION']) ? $assets['CONNECTION']: false;

        // CONNECTION INITIALIZE
        $_DBHOST     = $CONN_OVERRIDE&&isset($CONN_OVERRIDE['DB_HOST'])? $CONN_OVERRIDE['DB_HOST']:QDB_HOST;
        $_DBUSER     = $CONN_OVERRIDE&&isset($CONN_OVERRIDE['DB_USER'])? $CONN_OVERRIDE['DB_USER']:QDB_USER;
        $_DBPASS     = $CONN_OVERRIDE&&isset($CONN_OVERRIDE['DB_PASS'])? $CONN_OVERRIDE['DB_PASS']:QDB_PASS;
        $_DBNAME     = $CONN_OVERRIDE&&isset($CONN_OVERRIDE['DB_NAME'])? $CONN_OVERRIDE['DB_NAME']:(isset($assets['DB']) ? $assets['DB']:QDB_NAME);

        $this->_conn = $this->dbh = new PDO('mysql:host='.$_DBHOST.';dbname='.$_DBNAME,$_DBUSER, $_DBPASS);
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_modifier = time();

        if(!empty($assets)):
            $this->_table_raw  = array_key_exists('TABLE' ,$assets)     ? $assets['TABLE']     : false;
            $this->_join       = array_key_exists('JOIN', $assets)      ? $assets['JOIN']      : false;
            $this->_cols       = array_key_exists('COLS'  ,$assets)     ? $assets['COLS']      : false;
            $this->_key        = array_key_exists('KEY'   ,$assets)     ? $assets['KEY']       : false;
            $this->_sub        = array_key_exists('SUB_KEY' ,$assets)   ? $assets['SUB_KEY']   : false;
            $this->_group      = array_key_exists('GROUP' ,$assets)     ? $assets['GROUP']     : false;
            $this->_order      = array_key_exists('ORDER' ,$assets)     ? $assets['ORDER']     : false;
            $this->_limit      = array_key_exists('LIMIT', $assets)     ? $assets['LIMIT']     : false;
            $this->_format     = array_key_exists('FORMAT', $assets)    ? $assets['FORMAT']    : false;

            # NEW ASSET INDEX
            $this->_auto_exec  = array_key_exists('AUTO_EXEC', $assets) ? $assets['AUTO_EXEC'] : true;
            $this->_init_exec  = array_key_exists('INIT_EXEC', $assets) ? $assets['INIT_EXEC'] : 'SET SESSION group_concat_max_len = 1000000'; // INITIALIZE THE EXECUTION OF THIS QUERY FOR LONG STRING CONCATS
            $this->_debug      = array_key_exists('DEBUG', $assets)     ? $assets['DEBUG']     : false;

            # RUN THE INITITAL EXECUTE IF SET
            $this->_init_exec?$this->INITIAL_EXECUTE():null;

            # RUN THE DEBUG IF SET
            $this->_debug?$this->PREPARE_DEBUG_MODE():null;

            $this->PROCESS_TABLE_NAME();
            if($this->_auto_exec):
                ($this->_table && ($this->_key || $this->_join)) ? $this->PROCESS_TABLE(): $this->SET_COLUMNS();
            endif;
        endif;
    }

    // DESTROY THE CONNECTION ON DESTRUCT. PDO AUTOMATICALLY DESTROYS THE CONNECTION WHEN THE SCRIPT END UNLESS THE CONNECTION IS A PERSISTENT CONNECTION
    public function __destruct(){
        $this->_conn = null;
        $this->dbh   = null;
    }

    private function PREPARE_DEBUG_MODE(){
        if(is_array($this->_debug)):
            foreach($this->_debug as $v):
                $this->_showquery[$v] = true;
            endforeach;
        else:
            $this->_showquery = array_fill_keys(array_keys($this->_showquery),true);
        endif;
    }

    private function INITIAL_EXECUTE(){
        if($this->_init_exec):
            $xprep =  $this->_conn->prepare($this->_init_exec);
            $xprep->execute();
            unset($xprep);
        endif;
    }

    private function PROCESS_TABLE_NAME($arrs=false){
        $table_array = !$arrs ? $this->_table_raw : $arrs;
        $_table = explode('::', $table_array);
        $_return      =  isset($_table[1])&&$_table[1] ? $_table[0].' '.$_table[1] : $_table[0];
        $this->_table =  $_table[0];
        return $_return;
    }
    private function PROCESS_JOIN_CLAUSE($arrs = array()){
        $str = '';
        if(!isset($arrs[0])):
            $join_type  = isset($arrs['TYPE']) ? $arrs['TYPE'] . ' JOIN ' : ' INNER JOIN ';
            $join_table = $this->PROCESS_TABLE_NAME($arrs['TABLE']);
            $join_on    = $arrs['ON'];
            $str        = ' '.$join_type .  $join_table .' ON '. $join_on;
        else:
            foreach($arrs as $arr):
                $join_type  = isset($arr['TYPE']) ? $arr['TYPE'] . ' JOIN ' : ' INNER JOIN ';
                $join_table = $this->PROCESS_TABLE_NAME($arr['TABLE']);
                $join_on    = $arr['ON'];
                $str        .= ' '.$join_type .  $join_table .' ON '. $join_on;
            endforeach;
        endif;
        return $str;
    }
    private function PROCESS_GROUP_BY_CLAUSE($clause){
        $str = ' GROUP BY ' . $clause . ' ';
        return $str;
    }
    private function PROCESS_COLUMNS_ARRAY($arrs = array()){
        $str = ''; $gcStr='';
        foreach($arrs as $key => $arr):
            if($key==='GROUP_CONCAT'):
                if(is_array($arr)):// ignore case when alias used as key
                    end($arr);
                    $last = key($arr);
                    $gcStr .= '"{",';
                    foreach($arr as $k =>$v):
                        $gcStr .= '"\"'.$v.'\":\"",';
                        $gcStr .= ''.$v.'';
                        $gcStr .= ',"\"",';
                        if($last!== $k):
                            $gcStr .= '",",';
                        endif;
                    endforeach;
                    $gcStr .= '"}"';
                    $str .= "GROUP_CONCAT(concat(".$gcStr.")),";
                endif;
            else:
                $str .= !is_numeric($key) ? 'CONCAT('.$arr.') AS ' . $key . ',' : $arr .',';
            endif;
        endforeach;
        return rtrim($str,',');
    }
    private function PROCESS_KEYS_ARRAY($arrs = array(),$exclude=array())
    {
        $str = ' WHERE ';
        if(!is_array($arrs) && $arrs):
            $str .= '1';
        else:
            foreach($arrs as $key => $arr):
                //    echo 'k:' . $key . ' <br />';
                if(!in_array($key, array_keys($exclude))):
                    $temp_key = explode('::', $key);
                    $temp_key_name = explode('.',$temp_key[0]);
                    $key_name = isset($temp_key_name[1])&&$temp_key_name[1]?$temp_key_name[1]:$temp_key_name[0];
                    $process  = isset($temp_key[1])&&$temp_key[1]?$temp_key[1]:' = ';
                    $str .= $temp_key[0] .' '.$process .' :' . $key_name . ' AND ';
                endif;
            endforeach;
        endif;
        return rtrim($str,'AND ');
    }
    // PROCESS THE SUBKEYS TO ACCEPT BOTH STRING AND STRINGS WITH BIND
    private function PROCESS_SUB_KEY(){
        $retval  = '';
        $tmp_subkey = array();
        if(is_array($this->_sub)):
            $retval = array_key_exists('QUERY',$this->_sub)?' '.$this->_sub['QUERY'].' ':' '.$this->_sub['CLAUSE'].' ';
            $tmp_subkey = isset($this->_sub['PARAM'])?$this->_sub['PARAM']:array();
            if(!is_array($this->_key)): $this->_key=array(); endif; //handles case when key=1
            foreach($tmp_subkey as $k => $v):
                // if(count($this->_key)>0 && array_key_exists($k,$this->_key)):
                $new_key = $k."_".$this->_modifier;
                $this->_tmpsubkey[$new_key] = $v ; // COLLECT ALL TEMPORARY SUB KEYS
                $this->_key[$new_key]=$v;
                $retval = str_replace(":".$k,":".$new_key,$retval);
                /* else:
                     $this->_key[$k]=$v;
                     $this->_tmpsubkey[$k] = $v;
                 endif; // */
            endforeach;
        elseif(is_string($this->_sub)):
            $retval = ' '.$this->_sub.' ';
        else:
            $retval = false;
        endif;

        return $retval;
    }
    private function CLEANUP_KEYS($arrs=array()){
        $clean = array();
        $keys  = $arrs;
        foreach($keys as $k => $v):
            $_index =explode('::',$k);
            $_alias_index = explode('.',$_index[0]);
            $_final_index = isset($_alias_index[1])&&$_alias_index[1]?$_alias_index[1]:$_alias_index[0];
            $clean[$_final_index] = $v;
        endforeach;
        return $clean;
    }
    private function PROCESS_ORDER(){
        $str=' ORDER BY ';
        $_order = explode('::',  $this->_order);
        $_by = isset($_order[1])?$_order[1]:'ASC';
        $str .= $_order[0].' '.$_by.' ' ;
        return $str;
    }
    private function PROCESS_LIMIT_CLAUSE(){
        $str=' LIMIT ';
        $str .= $this->_limit ? $this->_limit : '';
        return $str;
    }
    private function SET_COLUMNS(){
        $this->_cols = is_array($this->_cols) ? $this->_cols : ($this->_cols ? explode(',',$this->_cols) : false);
        if($this->_cols):
            foreach($this->_cols as $key => $col):
                if(!is_array($col)):
                    $this->_columns[trim($key)] = trim($col);
                else:
                    $this->_columns[trim($key)] = $col;
                endif;
            endforeach;
        else:
            $prepared = $this->_conn->prepare("SHOW COLUMNS FROM " .  $this->_table);
            $prepared->execute();
            while($row = $prepared->fetch()):
                $this->_columns[$row['Field']] = '';
            endwhile;
        endif;
    }
    private function PROCESS_TABLE(){
        $this->SET_COLUMNS();
        $join    = $this->_join                           ? $this->PROCESS_JOIN_CLAUSE($this->_join)     :'';
        $columns = $this->_cols                           ? $this->PROCESS_COLUMNS_ARRAY($this->_columns):'*';
        $keys    = $this->_key                            ? $this->PROCESS_KEYS_ARRAY($this->_key)       :'';
        $sub     = $this->_sub                            ? $this->PROCESS_SUB_KEY()                     :'';//move before to handle array merge
        $group   = $this->_group                          ? $this->PROCESS_GROUP_BY_CLAUSE($this->_group):'';
        $order   = $this->_order                          ? $this->PROCESS_ORDER()                       :'';
        $limit   = $this->_limit                          ? $this->PROCESS_LIMIT_CLAUSE()                :'';
        $query    = 'SELECT ' . $columns . ' FROM '. $this->PROCESS_TABLE_NAME() . $join . $keys . $sub . $group . $order . $limit;
        $this->_query_string = $query;
        if($this->_showquery['SELECT']):echo $this->debug_display($query);endif; # SHOWS THE QUERY STRING IF SELECT OR ALL IS SET TO TRUE
        $prepared = $this->_conn->prepare($query);
        ((!is_array($this->_key) && $this->_key)  || ((!is_array($this->_key) && $this->_key) && $this->_join) || ($this->_join && !(is_array($this->_key) && $this->_key))  ) ? $prepared->execute() : $prepared->execute($this->CLEANUP_KEYS($this->_key));
        $result   =  $prepared->fetchAll(PDO::FETCH_ASSOC);
        foreach($result as $key => $values):
            foreach($values as $keyVal => $value):
                if(is_array($this->_format) && in_array($keyVal,array_keys($this->_format))):
                    $value = eval('return(' . $this->_format[$keyVal] .  ');');
                endif;
                $this->_result[$key][$keyVal] = trim($value);
            endforeach;
        endforeach;
        $this->fetch = isset($this->_result[0])? (object) $this->_result[0]:false;
        return $prepared;
    }
    private function EXECUTE_QUERY(){
        $prepared = $this->_conn->prepare($this->_query_string);
        ((!is_array($this->_key) && $this->_key)  || ((!is_array($this->_key) && $this->_key) && $this->_join) || ($this->_join && !(is_array($this->_key) && $this->_key))  ) ? $prepared->execute() : $prepared->execute($this->CLEANUP_KEYS($this->_key));
        return $prepared;
    }
    private function PROCESS_DATA_SET($ARRS=array()){
        $str = '';
        if(!empty($ARRS)):
            foreach($ARRS as $key => $arr):
                $str .= str_replace($this->_modifier,'',$key) . '=:'. $key . ',';
            endforeach;
        endif;
        return rtrim($str,',');
    }
    private function INSERT_QUERY($ARRS = array()){



        $data   = $this->PROCESS_DATA_SET($ARRS);
        $query  = 'INSERT INTO ' . $this->_table . ' SET ' . $data;
        $this->_query_string = $query;

        if($this->_showquery['INSERT']):echo $this->debug_display($query);endif;


        $prepared   = $this->_conn->prepare($query);
        $result     = $prepared->execute($ARRS);
        return $result;
    }
    private function UPDATE_QUERY($ARRS = array()){

        $alterARRS = array();
        foreach($ARRS as $key => $val): $alterARRS[$key.$this->_modifier] = $val; endforeach;
        $data = $this->PROCESS_DATA_SET($alterARRS);
        $keys = $this->_key ? $this->PROCESS_KEYS_ARRAY($this->_key,$this->_tmpsubkey):'';
        $subkeys = $this->_sub ? $this->PROCESS_SUB_KEY() : '';
        $bindARRs = array_merge($alterARRS,$this->CLEANUP_KEYS($this->_key));

        $query = 'UPDATE ' . $this->_table . ' SET ' . $data . $keys . $subkeys;
        $this->_query_string = $query;

        if($this->_showquery['UPDATE']):echo $this->debug_display($query);endif; # SHOWS THE QUERY STRING IF UPDATE OR ALL IS SET TO TRUE

        $prepared = $this->_conn->prepare($query);
        $result = $prepared->execute($bindARRs);
        return $result;
    }
    private function DELETE_QUERY(){
        $result = false;
        $keys = $this->_key ? ( is_array($this->_key) ? $this->PROCESS_KEYS_ARRAY($this->_key,$this->_tmpsubkey):false) : false;
        $sub  = $this->_sub ? $this->PROCESS_SUB_KEY():'';
        if($keys):
            $query = 'DELETE FROM ' . $this->_table . $keys . $sub;

            if($this->_showquery['DELETE']):echo $this->debug_display($query);endif;

            //$this->pre($this->CLEANUP_KEYS($this->_key));

            $prepared = $this->_conn->prepare($query);
            $result = $prepared->execute($this->CLEANUP_KEYS($this->_key));
            $this->_query_string = $query;
        endif;
        return $result;
    }
    private function RUN_QUERY($clause,$params){
        $prepared = $this->_conn->prepare($clause);
        $prepared->execute($params);
        return $prepared;
    }
    private function INSERT_MULTIPLE_QUERY($ARRS = array()){
        $raw_cols = '(`';
        foreach($ARRS[0] as $key1 => $value):
            $raw_cols .= $key1.'`,`';
        endforeach;
        $final_cols = rtrim($raw_cols,'`,`') . '`)';
        $ctr1=0;  $raw_vals='';
        foreach($ARRS as $ARR_VALUE):
            $raw_vals .= '(';
            foreach($ARR_VALUE as $key => $value): $raw_vals .= ':'.$key.$this->_modifier.'_'.$ctr1.','; endforeach;
            $raw_vals  = rtrim($raw_vals,',');
            $raw_vals .= '),';
            $ctr1++;
        endforeach;
        $final_vals = rtrim($raw_vals,',');
        $ctr2 = 0; $param = array();
        foreach($ARRS as $ARR_PARAM):
            foreach($ARR_PARAM as $key_param => $value_param):$param[$key_param.$this->_modifier.'_'.$ctr2] = $value_param; endforeach;
            $ctr2++;
        endforeach;
        $clause = 'INSERT INTO ' . $this->_table . $final_cols . ' VALUES ' . $final_vals;
        $result = $this->RUN_QUERY($clause, $param);
        return $result ? true:false;
    }

    private function debug_display($message){ return '<h6 class="hidden">'.$message.'</h6>';}

# PUBLIC FUNCTIONS
    public function fetch($key,$place=0){return !empty($this->_result)?$this->_result[$place][$key]:false;}
    public function result(){return $this->EXECUTE_QUERY();}
    public function fetchRow($place=0){ return !empty($this->_result)?$this->_result[$place]:false;}
    public function fetchAll(){return !empty($this->_result)?$this->_result:false;}
    public function lastId(){ return $this->_conn->lastInsertId();}
    public function save($data,$action='AUTO'){
        $retval = false;
        switch(strtoupper($action)):
            case 'AUTO'  : if(!$this->_auto_exec): ($this->_table && ($this->_key || $this->_join)) ? $this->PROCESS_TABLE(): $this->SET_COLUMNS(); endif;
                $retval = $this->_key && (is_array($this->_key) || ($this->_key==1 && $this->_sub)) && !empty($this->_result) ?  $this->UPDATE_QUERY($data) : (empty($this->_result)?$this->INSERT_QUERY($data):false);
                break;
            case 'INSERT': $retval =$this->INSERT_QUERY($data); break;
            case 'UPDATE': $retval =$this->UPDATE_QUERY($data); break;
            default : $retval = false; break;
        endswitch;

        return $retval; // $this->_key && (is_array($this->_key) || ($this->_key==1 && $this->_sub)) && !empty($this->_result) ?  $this->UPDATE_QUERY($data) : (empty($this->_result)?$this->INSERT_QUERY($data):false);
    }
    public function save_many($multi_data = array()){ return $this->INSERT_MULTIPLE_QUERY($multi_data);}
    public function delete(){ return $this->DELETE_QUERY();}
    public function numRows(){ return count($this->_result);}
    public function run($clause,$params=array()){ return $this->RUN_QUERY($clause,$params);}
    public function showLastQuery(){ return $this->_query_string ? $this->_query_string : false;}
    public function pre($array){    echo'<pre>'; print_r($array); echo'</pre>'; }
    public function clean($string){  return $this->ESCAPE_STR($string); }
    public function render(){ $this->PROCESS_TABLE(); }

} // END CLASS
?>
