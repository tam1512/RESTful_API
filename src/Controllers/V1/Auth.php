<?php
namespace App\Controllers\V1;
use App\Models\BlacklistToken;
use App\Models\RefreshToken;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class Auth {
   public function login() {
      $email = input('email');
      $password = input('password');

      if(!$email || !$password) {
         return errorResponse(400, "Vui lòng nhập email và mật khẩu.");
      }
      $userModel = new User();
      $user = $userModel->getUser($email, 'email');
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