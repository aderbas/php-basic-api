<?php
namespace core\util;

class Hash{
  /** character */
  private static $MAP = "0123456789abcdef";
    
  /**
   * Compare HASH sent by client and hash string 
   * password from dabatase
   * 
   * @param $hashnonce - Hash create by client
   * @param $password - hash string from database
   * @return boolean
   */
  public static function compare($hashnonce, $password){
    $nonce = substr($hashnonce, 32, 32);
    $cryptnonce = md5($nonce);
    if(md5( $password . $cryptnonce ) . $nonce == $hashnonce)
      return true;
    return false;
  }

  /**
   * Create HASH Nonce for put in session object
   * 
   * @return string nonce 32bits
   */
  public static function nonce(){
    $i = 0;
    $n = "";
    for(; $i < 32; $i++)
      $n .= self::$MAP{mt_rand(0, (strlen(self::$MAP) - 1))};
    return $n;
  }  
}