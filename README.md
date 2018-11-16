# php-basic-api
PHP Basic API using Token

## Getting Started
Assuming you understand the basics of the Apache server (or any other web server), Postgres database creation, basic classes/namespaces in PHP. Clone the project to the specific location of your web server. Apache like `/var/www/html`

## Creating a new Controller
All public functions are in a controller class. The functions will be used as the endpoint of the API. Crud operations are inherited by ControllerBase and must be overwritten if it is to be used.

BaseController.php
```
<?php

namespace core\controller;

abstract class BaseController implements interfaces\Crud{
  
  public $api;

  // GET /api/<router>/
  public function getList(){
    throw new \Exception('Not alowed');
  }
  // POST /api/<router>/
  public function save(){ 
    throw new \Exception('Not alowed');
  }
  // GET /api/<router>/:id
  public function get($id){
    throw new \Exception('Not alowed');
  }
  // PUT /api/<router>/
  public function remove($id){
    throw new \Exception('Not alowed');
  }
}
```
Exemple of use (BookController.php) 
```
<?php
namespace core\controller;

class BookController extends BaseController{
  public function __construct($api){
    $this->api = $api;
  }
}
```
Override method `getList()` (/api/book/)
```
/**
 * Get users
 * @Override from BaseController
 */
public function getList(){
  $q = "SELECT * FROM book";
  try{
    return $this->api->db->execute($q));
  }catch(\Exception $e){
    throw $e;
  }
}
```
Add new method to BookController.php (/api/book/author/:author)
```
public function author($author){
  $q = "SELECT * FROM book WHERE author = ?";
  try{
    return $this->api->db->execute($q, array($author));
  }catch(\Exception $e){
    throw $e;
  }  
}
```
## Register a new Controller
All controllers are registered in class MyAPI.php, open `/core/MyAPI.php` and put new router on `__construct`
```
public function __construct($request, $origin) {
  ...
  parent::registerRouter('/book', new controller\BookController($this));
  ...
}
```

## Reference
http://coreymaynard.com/blog/creating-a-restful-api-with-php/
