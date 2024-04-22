<?php
namespace App\Middlewares;

use App\Models\RateLimit;
use App\Models\RequestLog;
use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

class RateLimitMiddleware implements IMiddleware {
    private $modelRequestLog;
    private $modelRateLimit;
    private $limitRequest;
    private $limitPer;

    public function __construct() {
      $this->modelRequestLog = new RequestLog();
      $this->modelRateLimit = new RateLimit();
      $this->limitRequest = env("LIMIT_REQUEST");
      $this->limitPer = env("LIMIT_REQUEST_PER");
    }
    public function handle(Request $request): void 
    {
      $currentRateLimit = $this->getCurrentRateLimit();
      
      // if($currentRateLimit && $currentRateLimit['request_number'] >= $this->limitRequest) {
      //   $seconds = time() - strtotime($currentRateLimit['start_time']);
      //   if($seconds >= $this->limitPer) {
      //     $this->reset();
      //   } else {
      //     errorResponse(429, "Rate limit");
      //   }
      // } else {
      //   $this->createLog();
      //   $count = $this->getCountLog();
      //   $this->updateRateLimit($count);
      // }

      if($currentRateLimit) {
        $seconds = time() - strtotime($currentRateLimit['start_time']);
        if($seconds >= $this->limitPer) {
          $this->reset();
          $this->handleData();
        } else {
          if($currentRateLimit['request_number'] >= $this->limitRequest) {
            errorResponse(429, "Rate limit");
          } else {
            $this->handleData();
          }
        }
      } else {
        $this->handleData();
      }
    }

    private function getIp() {
      return request()->getIp();
   }
   
   private function createLog() {
    $this->modelRequestLog->create([
      'ip_address' => $this->getIp()
    ]);
   }

   private function getCountLog() {
    return $this->modelRequestLog->getCountLog($this->getIp());
   }

   private function updateRateLimit($count) {
    $data = [
      'ip_address' => $this->getIp()
    ];
    if($count == 0) {
      $data = array_merge($data, [
        'request_number' => $count,
        'start_time' => date('Y-m-d H:i:s')
      ]);
    } else {
      $data['request_number'] = $count;
    }
    $this->modelRateLimit->updateRateLimit($data, $this->getIp());
   }

   private function getCurrentRateLimit() {
    return $this->modelRateLimit->getRateLimit($this->getIp());
   }

   private function reset() {
    $ip = $this->getIp();
    $this->modelRequestLog->deleteLog($ip);
    $this->modelRateLimit->deleteRateLimit($ip);
   }

   private function handleData() {
    $this->createLog();
    $count = $this->getCountLog();
    $this->updateRateLimit($count);
   }
}