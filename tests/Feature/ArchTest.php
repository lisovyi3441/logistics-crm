<?php

declare(strict_types=1);

arch('strict types and no debug calls')
    ->expect(['dd', 'dump', 'ray'])
    ->not->toBeUsed();

arch('models extend eloquent')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Models\User'); // User extends Authenticatable

arch('controllers extend base controller')
    ->expect('App\Http\Controllers')
    ->toExtend('App\Http\Controllers\Controller');
