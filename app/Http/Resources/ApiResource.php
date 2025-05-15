<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResource extends JsonResource
{
    public $success;
    public $message;

     public function __construct($success, $message, $resource)
     {
         parent::__construct($resource);
         $this->success  = $success;
         $this->message = $message;
     }
    
    public function toArray(Request $request): array
    {
        return [
            'success'   => $this->success,
            'message'   => env('APP_DEBUG') ? $this->message : 'INTERNAL SERVER ERROR',
            'data'      => $this->resource
        ];
    }
    
}
