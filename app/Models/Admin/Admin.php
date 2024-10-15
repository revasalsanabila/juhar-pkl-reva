<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Database\Eloquent\Model;

class Admin extends  Authenticatable
{
    use HasFactory;

    protected $table ='admin';

    protected $filelable = [
        'username',
        'password',
    ];

    protected $hiden = [
        'password'
    ];
}
