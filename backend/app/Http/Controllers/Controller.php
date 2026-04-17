<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Schema;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Check if a database table exists.
     */
    protected function tableExists(string $tableName): bool
    {
        try {
            return Schema::hasTable($tableName);
        } catch (\Throwable) {
            return false;
        }
    }}