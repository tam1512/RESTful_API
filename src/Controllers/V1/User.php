<?php
namespace App\Controllers\V1;
use App\Models\User as UserModel;
use Rakit\Validation\Validator;
use App\Transformers\User as UserTransformer;

class User {
   private static $model;

   public function __construct() {
      if(!self::$model) {
         self::$model = new UserModel();
      }
   }
   public function index() {
      $sort = input('sort') ?? 'id';
      $order = input('order') ?? 'desc';
      $status = input('status');
      $query = input('query');
      $limit = (int)input('limit');
      $page = input('page') ?? 1;
      $offset = 0;
      if($limit) {
          $offset = ($page - 1) * $limit;
      }
      $count = self::$model->countRows(compact('sort', 'order', 'status', 'query'));
      $users = self::$model->getUsers(compact('sort', 'order', 'status', 'query', 'limit', 'offset'));
      $userTransformer = new UserTransformer($users, true);
      return successResponse(data:$userTransformer, meta:$limit ? [
         'current_page' => $page,
         'total_rows' => $count,
         'total_pages' => ceil($count/$limit)
      ] : []);
      // echo "users";
   }
   public function find($id) {
      $user = self::$model->getUser($id);
      if(!empty($user)) {
         $userTransformer = new UserTransformer($user);
         return successResponse(data:$userTransformer);
      }
      return errorResponse(404, 'User not found', [
         'error' => 'User not found',
         'id' => $id
      ]);
   }
   public function store() {
      $validator = new Validator;
      $validator->setMessages([
         'required' => ':attribute bắt buộc phải nhập.',
         'email:email' => ':attribute không hợp lệ.',
         'password:min' => ':attribute phải từ :min ký tự',
         'confirm_password:same' => ':attribute không trùng khớp'
      ]);
      $validation = $validator->make(input()->all(), [
         'fullname' => 'required',
         'email' => [
            'required', 
            'email',
            function($email) {
               $check = self::$model->checkExist('email', $email); 
               if($check) {
                  return ':attribute đã tồn tại.';
               }
               return true;
            }
         ],
         'password' => 'required|min:6',
         'confirm_password' => 'required|same:password'
      ]);
      $validation->setAliases([
         'fullname' => 'Tên',
         'email' => 'Email',
         'password' => 'Mật khẩu',
         'confirm_password' => 'Nhập lại mật khẩu',
      ]);
      $validation->validate();

      if($validation->fails()) {
         $errors = $validation->errors();
         return errorResponse(404, 'Bad request', $errors->firstOfAll());
      } else {
         $data = [
            'fullname' => input('fullname'),
            'email' => input('email'),
            'password' => password_hash(input('password'), PASSWORD_DEFAULT)
         ];
         $user = self::$model->create($data);
         $userTransformer = new UserTransformer($user);
         return successResponse(data:$userTransformer, status:201);
      }
   }
   public function update($id) {
      $method = request()->getMethod();
      return $method === 'put' ? $this->updatePut($id) : $this->updatePatch($id);
   }
   private function updatePatch($id) {
      $validator = new Validator;
      $validator->setMessages([
         'required' => ':attribute bắt buộc phải nhập.',
         'email:email' => ':attribute không hợp lệ.',
         'password:min' => ':attribute phải từ :min ký tự',
         'confirm_password:same' => ':attribute không trùng khớp'
      ]);

      $rules = [
         'fullname' => 'required',
         'email' => [
            'required', 
            'email',
            function($email) use($id) {
               $check = self::$model->checkExist('email', $email, $id); 
               if($check) {
                  return ':attribute đã tồn tại.';
               }
               return true;
            }
         ],
         'status' => [
            function ($status) {
               $include = [0, 1];
               if(!in_array($status, $include)) {
                  return ":attribute không hợp lệ.";
               }
            }
         ]
      ];
      if(input('password')) {
         $rules = array_merge($rules, [
         'password' => 'min:6',
         'confirm_password' => 'required|same:password'
         ]);
      }

      $validation = $validator->make(input()->all(), $rules);
      $validation->setAliases([
         'fullname' => 'Tên',
         'email' => 'Email',
         'status' => 'Trạng thái',
         'password' => 'Mật khẩu',
         'confirm_password' => 'Nhập lại mật khẩu',
      ]);
      $validation->validate();

      if($validation->fails()) {
         $errors = $validation->errors();
         return errorResponse(404, 'Bad request', $errors->firstOfAll());
      } else {
         $data = [
            'fullname' => input('fullname'),
            'email' => input('email')
         ];
         if(input('password')) {
            $data['password'] = password_hash(input('password'), PASSWORD_DEFAULT);
         }
         if(input('status') == 0 || input('status') == 1) {
            $data['status'] = input('status');
         }
         try {
            $status = self::$model->updateUser($data, $id);
            if($status) {
               $user = self::$model->getUser($id);
               if($user) {
                  $userTransformer = new UserTransformer($user);
                  return successResponse(data:$userTransformer);
               } else {
                  return errorResponse(404, 'User not found', [
                     'id' => $id
                  ]);
               }
            } else {
               return errorResponse(500, 'Server Error');
            }
         } catch (\Exception $e) {
            return errorResponse(500, 'Server Error');
         }
      }
   }
   private function updatePut($id) {
      /**
       * Kiểm tra trường nào được gửi lên
       * Xóa tất cả dữ liệu các trường còn lại trên CSDL
       */
      $validator = new Validator;
      $validator->setMessages([
         'required' => ':attribute bắt buộc phải nhập.',
         'email:email' => ':attribute không hợp lệ.',
         'password:min' => ':attribute phải từ :min ký tự',
         'confirm_password:same' => ':attribute không trùng khớp'
      ]);

      $rules = [];
      $data = [
         'fullname' => null,
         'email' => null,
         'status' => null
      ];

      if(input('fullname')) {
         $rules['fullname'] = 'min:2';
         $data['fullname'] = input('fullname');
      }
      if(input('email')) {
         $rules['email'] = [
            'email',
            function($email) use($id) {
               $check = self::$model->checkExist('email', $email, $id); 
               if($check) {
                  return ':attribute đã tồn tại.';
               }
               return true;
            }
         ];
         $data['email'] = input('email');
      }

      if(input('status') == 0 || input('status') == 1) {
         $rules['status'] = [
            function ($status) {
               $include = [0, 1];
               if(!in_array($status, $include)) {
                  return ":attribute không hợp lệ.";
               }
            }
         ];
         $data['status'] = input('status');
      }

      if(input('password')) {
         $rules = array_merge($rules, [
         'password' => 'min:6',
         'confirm_password' => 'required|same:password'
         ]);
         $data['password'] = password_hash(input('password'), PASSWORD_DEFAULT);
      }

      $validation = $validator->make(input()->all(), $rules);
      $validation->setAliases([
         'fullname' => 'Tên',
         'email' => 'Email',
         'status' => 'Trạng thái',
         'password' => 'Mật khẩu',
         'confirm_password' => 'Nhập lại mật khẩu',
      ]);
      $validation->validate();

      if($validation->fails()) {
         $errors = $validation->errors();
         return errorResponse(404, 'Bad request', $errors->firstOfAll());
      } else {
         try {
            $status = self::$model->updateUser($data, $id);
            if($status) {
               $user = self::$model->getUser($id);
               if($user) {
                  $userTransformer = new UserTransformer($user);
                  return successResponse(data:$userTransformer);
               } else {
                  return errorResponse(404, 'User not found', [
                     'id' => $id
                  ]);
               }
            } else {
               return errorResponse(500, 'Server Error');
            }
         } catch (\Exception $e) {
            return errorResponse(500, 'Server Error');
         }
      }
   }

   public function delete($id) {
      $user = self::$model->getUser($id);
      if($user) {
         $status = self::$model->deleteUser($id);
         if($status) {
            $userTransformer = new UserTransformer($user);
            return successResponse(data:$userTransformer);
         }
         return errorResponse(500, 'Server Error');
      }
      return errorResponse(404, 'User not found', [
         'error' => 'User not found',
         'id' => $id
      ]);
   }

   public function deletes() {
      $ids = input('ids');
      if(!$ids || !is_array($ids)) {
         return errorResponse(400, 'Bad Request', [
            'error' => 'Invalid',
            'required' => 'Is Array',
            'field' => 'ids',
            'current_value' => $ids
         ]);
      }
      $status = self::$model->deleteUsers($ids);
      var_dump($status);
      if($status) {
         return successResponse(data:$ids);
      }
      return errorResponse(500, 'Server Error');
   }
}