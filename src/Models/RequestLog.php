<?php
namespace App\Models;
use System\Core\Model;

class RequestLog extends Model {
   private $table = 'request_log';

   public function create($data) {
      return $this->db->table($this->table)->insert($data);
   }

   public function getCountLog($ipAddress) {
      return $this->db->table($this->table)->where('ip_address', '=', $ipAddress)->count();
   }

   public function deleteLog($ipAddress) {
      return $this->db->table($this->table)->where('ip_address', '=', $ipAddress)->delete();
   }
}