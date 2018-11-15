<?php

namespace core\database;

class Database{
/** Singleton instance */
private static $_instance;

/** PDO Link */
private static $_link;

/** Statment */
private $_stmt;

/** Transaction running */
private $_activeTransactions = 0;

/** params */
private static $_params = array(
  'adapter' => 'pgsql/mysql',
  'params' =>
    array (
      'host' 		  => 'localhost',
      'dbname' 	  => 'dbname',
      'username' 	=> 'username',
      'password' 	=> 'password'
    )
);

/** Options */
private static $options = array(
  \PDO::ATTR_PERSISTENT => true
);

/** Persistent connection? */
private static $_persistent = false;

/** Select object */
private $_select;

/**
 * Constructor default
 */
public function __construct($params = null){
  if(!is_null($params))
    $this->registry($params);
}

/**
 * Singleton implementation
 *
 * @param optional [mixed array $params]
 * @see registry($params)
 */
public static function getInstance($params = null){
  if( self::$_instance == null ){
    $params = is_null($params) ? self::$_params : $params;
    self::$_instance = new Database($params);
  }
  return self::$_instance;
}

/**
 * Clone the params to internal field $this->params
 *
 * @param mixed array $params
 * @throws Exception
 * @return void
 *
 * @example
 * $params:
 *
 * array( 'adapter' => 'pgsql',
 * 				'host' 		=> '192.0.0.1',
 *				'dbname' 	=> 'db_name',
 *				'username' 	=> 'user_name',
 *				'password' 	=> 'password'
 * )
 *
 * @author Aderbal Nunes
 */
public function registry($params){
  if(!is_array($params))
    throw new \Exception("The parameters must be in array");
  if(!isset($params["adapter"]))
    throw new \Exception("Specify the adapter for connection to the band (See PHP PDO)");
  self::$_params = array(
    'adapter' => $params["adapter"],
    'params' =>
      array (
        'host' 		  => $params["host"],
        'dbname' 	  => $params["dbname"],
        'username' 	=> $params["username"],
        'password' 	=> $params["password"]
      )
  );
  // try start connection data base
  $this->startPDO();
 }

/**
 * Try start database connection
 *
 *  @return void
 *  @author Aderbal Nunes
 */
private function startPDO(){
   try{
     self::$_link = new \PDO(
       self::$_params["adapter"].":host=".self::$_params["params"]["host"].
         ";dbname=".self::$_params["params"]["dbname"],
       self::$_params["params"]["username"],
       self::$_params["params"]["password"]
       //(self::$_persistent) ? self::$options : array()
     );
     self::$_link->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
   }catch (\Exception $e){
     echo $e->getMessage();
   }
 }

/**
 * Gets PDO Object
 *
 * @throws Exception
 * @return PDO Object
 * @author Aderbal Nunes
 */
public function db(){
  if(!isset(self::$_link))
    throw new \Exception("The link to the database was not started properly");
  return self::$_link;
}

/**
 * Close link whit database
 * @return void
 */
public static function close(){
  self::$_link = null;
  self::$_instance = null;
}

/**
 * Execute a custom query
 *
 * @param String $query
 * @param [mixed array $params]
 * @param [boolean $one] - if return one param
 * @return stdClass[] or false if no result
 */
public function execute($query, array $params = null, $one = false){
  try {
    $this->_stmt = $this->db()->prepare($query);
    // if erros
    if(!$this->_stmt){
      $err = $this->db()->errorInfo();
      throw new \Exception($err[2]);
    }
    // params type
    if(!is_null($params) && $this->hasKey($params)){
      foreach($params as $key => $val)
        $this->bind($key, $val);
      $this->_stmt->execute();
    }else{
      $this->_stmt->execute($params);
    }
    // result
    if($this->_stmt->rowCount() > 0){
      $result = $this->_stmt->fetchAll();
      return ($one) ? $result[0] : $result;
    }else return false;
  } catch (\Exception $e) {
    throw $e;
  }
}

/**
 * Bind value param
 * @param $key
 * @param $value
 */
public function bind($key, $value){
  if(is_null($key)){
    throw new \Exception("Key of value is null");
  }
  $type = false;
  if(is_int($value))
    $type = \PDO::PARAM_INT;
  elseif(is_bool($value)){
    $type = \PDO::PARAM_BOOL;
  }elseif(is_null($value))
    $type = \PDO::PARAM_NULL;
  elseif(is_string($value))
    $type = \PDO::PARAM_STR;

  $this->_stmt->bindParam($key, $value, $type);
}

/**
 * Execute a query and return number of affected rows
 * (update or delete for ex)
 * @param $query
 * @return number of affected rows
 * @throws Exception
 */
public function exec($query){
  try{
    return $this->db()->exec($query);
  }catch (Exception $e) {
    throw new Exception($e->getMessage());
  }
}

/**
 * Initiates a transaction
 * @return boolean - false if transaction is active
 */
public function beginTransaction(){
  return ($this->_activeTransactions++ > 0)? false: $this->db()->beginTransaction();
}

/**
 * Commits a transaction
 * @return void
 */
public function commit(){
  return (--$this->_activeTransactions > 0)? false: $this->db()->commit();
}

/**
 * Rolls back a transaction
 * @return void
 */
public function rollBack(){
  $this->_activeTransactions = 0;
  return $this->db()->rollBack();
}

/** Try to close connection */
public function __destruct(){
  self::close();
}

/** Util ############################################# */

/**
 * Check if array has key values
 * @param $arr
 * @return boolean
 */
  public function hasKey($arr){
    return (!is_null($arr) && count($keys = array_keys($arr)) > 0 && !is_int($keys[0]));
  }  
}