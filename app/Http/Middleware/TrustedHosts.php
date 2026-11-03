<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts;

class TrustedHosts extends TrustHosts
{
    protected function shouldSpecifyTrustedHosts()
    {
        // Password and verification links must not trust arbitrary Host headers, including on local installs.
        return true;
    }
}
