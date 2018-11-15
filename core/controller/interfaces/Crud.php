<?php
namespace core\controller\interfaces;

interface Crud{
  public function getList();
  public function save();
  public function get($id);
  public function remove($id);
}
