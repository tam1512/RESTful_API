<?php
namespace System\Core;
use \PDO;
use \Exception;
class Connection {
   private static $conn;
   

   private function __construct($configs) {
      try {
         if (class_exists('PDO')) {
            $dsn = $configs['dbDriver'] . ':dbname=' . $configs['dbName'] . ';host=' . $configs['dbHost'] . ';port=' . $configs['dbPort'];
      
            $options = [
               PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
               PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION //Đẩy lỗi vào ngoại lệ truy vấn
            ];

               self::$conn = new PDO($dsn, $configs['dbUser'], $configs['dbPass'], $options);
         }
      } catch (Exception $e) {
         echo $e->getMessage();
         exit();
      }
   }


   public static function getInstance($configs) {
      if(self::$conn == null) {
         new Connection($configs);
      }
      return self::$conn;
   }
}