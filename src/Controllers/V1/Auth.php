<?php
namespace App\Controllers\V1;
use App\Models\BlacklistToken;
use App\Models\RefreshToken;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Rakit\Validation\Validator;
class Auth {

   private static $userModel;
   public function __construct() {
      self::$userModel = new User();
   }
   public function login() {
      $email = input('email');
      $password = input('password');

      if(!$email || !$password) {
         return errorResponse(400, "Vui lòng nhập email và mật khẩu.");
      }
    
      $user = self::$userModel->getUser($email, 'email');
      if(!$user) {
         return errorResponse(404, "Tài khoản không tồn tại.");
      }

      $passwordHash = $user['password'];
      if(!password_verify($password, $passwordHash)) {
         return errorResponse(401, "Mật khẩu không đúng.");
      }
      
      //Tạo token JWT
      $payloadAccess = [
         'sub' => $user['id'],
         'iat' => time(),
         'exp' => time() + env('JWT_EXP')
     ];
      $payloadRefresh = [
         'sub' => $user['id'],
         'iat' => time(),
         'exp' => time() + env('JWT_REFRESH_EXP')
     ];
     $accessToken = JWT::encode($payloadAccess, env('JWT_SECRET'), 'HS256');

     $refreshToken = JWT::encode($payloadRefresh, env('JWT_REFRESH_SECRET'), 'HS256');
     $dataRefresh = [
      "refresh_token" => $refreshToken,
      "user_id" => $user['id']
     ];
     $refreshModel = new RefreshToken();
     $checkExist = $refreshModel->find($user['id']);
     if($checkExist) {
      $refreshModel->deleteToken($user['id']);
     }
     $refreshModel->create($dataRefresh);
     return successResponse(data: [
      'access_token' => $accessToken,
      'refresh_token' => $refreshToken
     ]);

   }

   public function profile() {
      return successResponse(data:\System\Core\Auth::getUser());
   }

   public function updateProfile() {
      $id = \System\Core\Auth::getUser()['id'];
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
               $check = self::$userModel->checkExist('email', $email, $id); 
               if($check) {
                  return ':attribute đã tồn tại.';
               }
               return true;
            }
         ]
      ];
      if(input('password')) {
         $rules = array_merge($rules, [
         'password' => 'min:6',
         'confirm_password' => 'required|same:password'
         ]);
      }
      $avatarPath = "";
      $avatar = input()->file('avatar');
      if($avatar) {
         $allow = ["image/jpeg", "image/png", "image/gif"];
         $type = $avatar->getMime();
         if(in_array($type, $allow)) {
            $destinationFilname = sprintf('%s.%s', uniqid(), $avatar->getExtension());
            $avatarPath = sprintf('/uploads/%s', $destinationFilname);
            $avatar->move(sprintf(".".$avatarPath));
         } else {
            $rules['avatar'] = [
               function(){
                  return ":attribute không hợp lệ";
               }
            ];
         }
      }

      $validation = $validator->make(input()->all(), $rules);
      $validation->setAliases([
         'fullname' => 'Tên',
         'email' => 'Email',
         'avatar'=> 'Ảnh đại diện',
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
         if($avatarPath) {
            $data['avatar'] = getPrefixLink().$avatarPath; 
         }
         try {
            $status = self::$userModel->updateUser($data, $id);
            if($status) {
               $user = self::$userModel->getUser($id);
               if($user) {
                  $userTransformer = new \App\Transformers\User($user);
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

   public function refresh() {
      $refreshToken = input('refresh_token');
      if(!$refreshToken) {
         return errorResponse(401, 'Unauthorize');
      } else {
         try {
            $decode = JWT::decode($refreshToken, new Key(env('JWT_REFRESH_SECRET'), 'HS256'));
            $resfreshModel = new RefreshToken();
            $token = $resfreshModel->find($refreshToken, 'refresh_token');
            if(!$token) {
               return errorResponse(401, 'Token invalid');
            }

            $payloadAccess = [
               'sub' => $decode->sub,
               'iat' => time(),
               'exp' => time() + env('JWT_EXP')
           ];
           $accessToken = JWT::encode($payloadAccess, env('JWT_SECRET'), 'HS256');
           return successResponse(data: [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
           ]);
         } catch (\Exception $e) {
            return errorResponse(401, 'Unauthorize', $e->getMessage());
         }

         
      }
   }

   public function logout() {
      $user = \System\Core\Auth::getUser();
      $token = $user['token'];
      $expire = $user['expire'];
      if($token && $expire) {
         $blacklist = new BlacklistToken();
         try {
            $blacklist->create([
               "token" => $token,
               "expire" => $expire,
            ]);
            $userTransformer = new \App\Transformers\User($user);
            return successResponse(data:$userTransformer);
         } catch (\Exception $e) {
            return errorResponse(401, "User logged out");
         }
      }
   }
}