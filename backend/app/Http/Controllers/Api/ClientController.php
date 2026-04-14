<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;

class ClientController extends Controller
{
    public function index()
    {
        return Client::query()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }
}
