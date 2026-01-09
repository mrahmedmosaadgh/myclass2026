<?php

namespace App\Http\Controllers\AdminV2;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class BaseV2Controller extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
        // Shared V2 initialization logic
    }
}
