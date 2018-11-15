<?php
namespace core;

use core\API;
use core\database\Database;
use \Firebase\JWT\JWT;

class MyAPI extends API{

  public $db;
  public $user;

  protected $config;

  protected $unlessRouter = array(
    '/user/auth',
    '/version'
  );

  public function __construct($request, $origin) {
    parent::__construct($request);
    // config file
    $this->config = parse_ini_file('config.ini', true);
    
    if(!$this->validateRequest("/$request")){
      throw new \Exception('Invalid User Token');
    }
    // Database
    try{
      $this->db = Database::getInstance($this->config['database']);
    }catch(\Exception $e){
      throw $e;
    }
    // Controllers
    parent::registerRouter('/user', new controller\UserController($this));
  }
  
  /**
   * Version endpoint
   */
  protected function version() {
    if ($this->method == 'GET') {
      return array("label" => "MyAPI REST API", "version" => "0.1.5");
    } else {
      return "Only accepts GET requests";
    }
  }

  /**
   * Sign in 
   */
  public function signIn($user){
    $token = array(
      "user" => $user,
      "exp" => strtotime(date("Y-m-d", mktime()) . " + 5 day")
    );
    return JWT::encode($token, $this->config['key']['token']);
  }

  /**
   * Validate request
   */
  private function validateRequest($request){
    if(!in_array($request, $this->unlessRouter)){
      // check token
      $headers = apache_request_headers();
      if(array_key_exists('Authorization', $headers)){
        try{
          $decoded = JWT::decode(trim(str_replace('Bearer', '', $headers['Authorization'])), $this->config['key']['token'], array('HS256'));
          $this->user = $decoded->user; 
        }catch(\Exception $e){
          //error_log($e->getMessage());
          return false;
        }
        return true;
      }
      return false;
    }
    return true;
  }
}