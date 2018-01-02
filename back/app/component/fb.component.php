<?php

namespace App\Components;

use App\Models\Token;
use App\Models\User;

class FBComponent extends Component
{
    public function __invoke($request, $response, $next)
    {
        /** Check user authenticate **/
        return $next($request, $response);
    }
}