<?php 
namespace System\Core;
use \Exception;
use \PDO;
class Database {
   use QueryBuilder;
   private $__conn = null;
   private $sql = null;
   public function __construct($configs) {
      $this->__conn = Connection::getInstance($configs);
   }

   public function query($sql, $data=[], $statementStatus = false) {
      $this->sql = $sql;
      $query = false;
      try{
         $statement = $this->__conn->prepare($this->sql);
         if(empty($data)) {
            $query = $statement->execute();
         } else {
            $query = $statement->execute($data);
         }
      }catch(Exception $e){
         // echo $e->getMessage();
         // exit();
         errorResponse(500, 'Server Error', [
            'error' => $e->getMessage()
         ]);
      }

      if($statementStatus && $query) {
         return $statement;
      }

      return $query;
   }

   private function fetch($sql) {
      $statement = $this->query($sql, [], true);

      if(is_object($statement)) {
         return $statement;
      }
      return false;
   }

   function getRaw($sql) {
      $statement = $this->fetch($sql);
      // echo $sql;
      // echo "<br>";

      if(!empty($statement)) {
         return $statement->fetchAll(PDO::FETCH_ASSOC);
      }
      return false;
   }

   function firstRaw($sql) {
      $statement = $this->fetch($sql);

      if(!empty($statement)) {
         return $statement->fetch(PDO::FETCH_ASSOC);
      }
      return false;
   }

   function insertData($table, $dataInsert) {
      $arrKeys = array_keys($dataInsert);
      $fieldStr = implode(', ', $arrKeys);
      
      $valueStr = ':'.implode(', :', $arrKeys);
      
      $sql = "INSERT INTO $table($fieldStr) VALUES ($valueStr);";
      
      $this->resetQueryBuilder();
      return $this->query($sql, $dataInsert);
   }

   function updateData($table, $dataUpdate, $condition) {
      $updateStr = '';
      foreach($dataUpdate as $key => $value) {
         $updateStr .= $key.' = :'.$key . ', ';
      }
      $updateStr = rtrim($updateStr, ', ');

      if(empty($condition)) {
         $sql = "UPDATE $table SET $updateStr";
      } else {
         $sql = "UPDATE $table SET $updateStr WHERE $condition";
      }
      
      $this->resetQueryBuilder();
      return $this->query($sql, $dataUpdate);
   }
   function deleteData($table, $condition = '') {
      $sql = '';
      if(empty($condition)) {
         $sql = "DELETE FROM $table";
      } else {
         $sql = "DELETE FROM $table WHERE $condition";
      }
      $this->resetQueryBuilder();
      return $this->query($sql);
   }

   // Lấy ra số dòng truy vấn
   function getRows($sql) {
      $statement = $this->fetch($sql);
      if(!empty($statement)) {
         return $statement->rowCount();
      }
      return false;
   }

   // Lấy ra id vừa insert
   function lastInsertId() {
      return $this->__conn->lastInsertId();
   }

   function getPdo() {
      return $this->__conn;
   }
}