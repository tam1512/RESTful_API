<?php
namespace App\Models;
use System\Core\Model;

class RefreshToken extends Model {
   public function find($value, $field="user_id") {
      return $this->db->table('refresh_tokens')->where($field, '=', $value)->first();
   }

   public function create($data) {
      $this->db->table('refresh_tokens')->insert($data);
   }

   public function deleteToken($userId) {
      $this->db->table('refresh_tokens')->where('user_id', '=', $userId)->delete();
   }
}