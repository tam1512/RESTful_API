<?php 
namespace App\Models;
use System\Core\Model;
class User extends Model {
   public function getUsers($options = []) {
      extract($options);
      $user = $this->db->table('users')->orderBy($sort, $order);
      if($status === 1 || $status === 0 || $status === '1' || $status === '0') {
         $user->where('status', '=', $status);
      }
      if($query) {
         $user->where(function ($builder) use ($query) {
            $builder->where('fullname', 'like', "%$query%");
            $builder->orWhere('email', 'like', "%$query%");
         });
      }
      if($limit && isset($offset)) {
         $user->limit($limit, $offset);
      }
      return $user->get();
   }

   public function countRows($options = []) {
      extract($options);
      $user = $this->db->table('users')->orderBy($sort, $order);
      if($status === 1 || $status === 0 || $status === '1' || $status === '0') {
         $user->where('status', '=', $status);
      }
      if($query) {
         $user->where(function ($builder) use ($query) {
            $builder->where('fullname', 'like', "%$query%");
            $builder->orWhere('email', 'like', "%$query%");
         });
      }
      return $user->count();
   }
   public function getUser($id) {
      return $this->db->table('users')->where('status', '=', 1)->where('id', '=', $id)->get();
   }
   public function checkExist($field, $value) {
      $count = $this->db->table('users')->where($field, '=', $value)->count();
      return $count > 0;
   }

   public function create($data) {
      $this->db->table('users')->insert($data);
      $id = $this->db->lastId();

      $user = $this->db->table('users')->where('id', '=', $id)->first();
      if(!empty($user)) {
         unset($user['password']);
      }
      return $user;
   }
}