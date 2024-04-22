<?php
namespace System\Core;
trait QueryBuilder {
   private $tableName = '';
   private $where = '';
   private $field = '*';
   private $join = '';
   private $limit = '';
   private $orderBy = '';

   private $sqlPaginate = '';

   public function table($tableName) {
      $this->tableName = $tableName;
      return $this;
   } 

   public function select($field) {
      $this->field = $field;
      return $this;
   }

   public function where($field, $compare="", $value="") {
      if($field instanceof \Closure) {
         $this->where .= ' (';
         $callback = $field;
         call_user_func_array($callback, [$this]);
         $this->where .= ')';
      } else {
         if(empty($this->where) || $this->where == ' (') {
            $this->where .= " WHERE $field $compare '$value'";
         } else {
            $this->where .= " AND $field $compare '$value'";
         }
      }
      return $this;
   }

   public function orWhere($field, $compare='', $value='') {
      if($field instanceof \Closure) {
         $this->where .= ' (';
         $callback = $field;
         call_user_func_array($callback, [$this]);
         $this->where .= ')';
      } else {
         if(empty($this->where) || $this->where == ' (') {
            $this->where .= " WHERE $field $compare '$value'";
         } else {
            $this->where .= " OR $field $compare '$value'";
         }
      }
      return $this;
   }

   public function whereLike($field, $value) {
      if(empty($this->where) || $this->where == ' (') {
         $this->where .= " WHERE $field LIKE '$value'";
      } else {
         $this->where .= " AND $field LIKE '$value'";
      }
      return $this;
   }

   public function whereIn($field, $array) {
      if(empty($this->where) || $this->where == ' (') {
         $this->where .= " WHERE $field IN (".implode(',', $array).")";
      } else {
         $this->where .= " AND $field IN (".implode(',', $array).")";
      }
      return $this;
   }
   public function limit($number, $offset = 0) {
      $this->limit = " LIMIT $offset, $number";
      return $this;
   }

   public function orderBy($field, $type='ASC') {
      $fieldArr = array_filter(explode(',', $field));
      if(!empty($fieldArr) && count($fieldArr) >= 2) {
         //ORDER BY id ASC, fullname DESC (multi order by)
         $this->orderBy = " ORDER BY ".implode(', ', $fieldArr);
      } else {
         //ORDER BY id ASC
         $this->orderBy = " ORDER BY $field $type";
      }
      return $this;
   }
   public function join($name, $condition) {
         $this->join .= " INNER JOIN $name ON $condition";
         return $this;
   }

   public function leftJoin($name, $condition) {
         $this->join .= " LEFT JOIN $name ON $condition";
         return $this;
   }

   public function rightJoin($name, $condition) {
         $this->join .= " RIGHT JOIN $name ON $condition";
         return $this;
   }

   public function fullJoin($name, $condition) {
         $this->join .= " FULL OUTER JOIN $name ON $condition";
         return $this;
   }

   public function get() {
      $sql = "SELECT $this->field FROM $this->tableName $this->join $this->where $this->orderBy $this->limit;";
      
      $this->sqlPaginate = $this->handleWhereGroup("SELECT $this->field FROM $this->tableName $this->join $this->where;");

      $sql = $this->handleWhereGroup($sql);
      $this->resetQueryBuilder();
      return $this->getRaw($sql);
   }

   public function first() {
      $sql = "SELECT $this->field FROM $this->tableName $this->join $this->where $this->orderBy $this->limit;";
      $sql = $this->handleWhereGroup($sql);
      $this->resetQueryBuilder();
      return $this->firstRaw($sql);
   }

   public function insert($data) {
      return $this->insertData($this->tableName, $data);
   }

   public function lastId() {
      return $this->lastInsertId();
   }

   public function update($data) {
      $condition = trim(trim($this->where), 'WHERE');
      return $this->updateData($this->tableName, $data, $condition);
   }

   public function delete() {
      $condition = trim(trim($this->where), 'WHERE');
      return $this->deleteData($this->tableName, $condition);
   }

   public function count() {
      $sql = "SELECT $this->field FROM $this->tableName $this->join $this->where $this->orderBy $this->limit;";
      $sql = $this->handleWhereGroup($sql);
      $this->resetQueryBuilder();
      return $this->getRows($sql);
   }
   private function resetQueryBuilder() {
      $this->tableName = '';
      $this->where = '';
      $this->field = '*';
      $this->join = '';
      $this->limit = '';
      $this->orderBy = '';
   }

   private function handleWhereGroup($sql) {
     $sql = preg_replace('~\(\s*OR~', 'OR (', $sql);
     $sql =preg_replace('~\(\s*AND~', 'AND (', $sql);
     $sql = preg_replace('~\(\s*WHERE~', 'WHERE (', $sql);

     return $sql;
   }
}
//26/10/2020