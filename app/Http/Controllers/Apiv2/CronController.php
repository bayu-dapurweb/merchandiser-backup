<?php

namespace App\Http\Controllers\Apiv2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CronController extends Controller
{
    public function refreshtoken()
    {
        \App\Service\IsellerTokenService::refresh();
    }
}
