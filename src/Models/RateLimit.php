<?php
namespace App\Models;
use System\Core\Model;

class RateLimit extends Model {
   private $table = 'rate_limit';
   

   public function create($data) {
      return $this->db->table($this->table)->insert($data);
   }

   public function updateRateLimit($data, $ipAddress) {
      $count = $this->db->table($this->table)->where('ip_address', '=', $ipAddress)->count();
      if($count > 0) {
         return $this->db->table($this->table)->where('ip_address', '=', $ipAddress)->update($data);
      } else {
         return $this->create($data);
      }
   }

   public function getRateLimit($ipAddress) {
      return $this->db->table($this->table)->where('ip_address', '=', $ipAddress)->first();
   }

   public function deleteRateLimit($ipAddress) {
      return $this->db->table($this->table)->where('ip_address', '=', $ipAddress)->delete();
   }
}