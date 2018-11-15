<?php

namespace core\controller;

abstract class BaseController implements interfaces\Crud{
  
  public $api;

  public function getList(){
    throw new \Exception('Not alowed');
  }
  public function save(){ 
    throw new \Exception('Not alowed');
  }
  public function get($id){
    throw new \Exception('Not alowed');
  }
  public function remove($id){
    throw new \Exception('Not alowed');
  }
}