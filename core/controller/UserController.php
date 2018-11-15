<?php
namespace core\controller;

use \Firebase\JWT\JWT;
use core\util\Hash;

class UserController extends BaseController{

  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;

  public function __construct($api){
    $this->api = $api;
  }

  /**
   * Autehticate
   */
  public function auth(){
    if($this->api->method == 'POST'){
      if(empty($this->api->request->email) || empty($this->api->request->key)){
        throw new \Exception("Invalid parameter");
      }
      // try get user
      $q  = "SELECT id,name,password,email,phone FROM user WHERE email = ?";
      try{
        $user = $this->api->db->execute($q, array($this->api->request->email), true);
        if(!is_bool($user)){
          // check pwd
          if(Hash::compare($this->api->request->key, $user->password)){
            unset($user->senha);
            return $this->api->signIn($user);
          }else{
            throw new \Exception('User or password not match');
          }
        }else{
          throw new \Exception('User not found');
        }
      }catch(\Exception $e){
        throw $e;
      }
    }else{
      throw new \Exception("Only POST");
    }
  }

}