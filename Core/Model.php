<?php 
namespace System\Core;
class Model extends Database{
   protected $db;
   public function __construct() {
      $dbHost = env('DB_HOST');
      $dbName = env('DB_DATABASE');
      $dbPort = env('DB_PORT');
      $dbUser = env('DB_USERNAME');
      $dbPass = env('DB_PASSWORD');
      $dbDriver = env('DB_DRIVER');

      $configs = compact('dbHost', 'dbName', 'dbPort', 'dbUser', 'dbPass', 'dbDriver');

      $this->db = new Database($configs);
   }
}