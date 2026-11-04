<?php

namespace Tests\Feature\Security;

use Illuminate\Support\Facades\Route;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class ViewRouteContractTest extends TestCase
{
    public function test_every_literal_route_referenced_by_a_view_is_registered(): void
    {
        $count = 0;
        foreach ((new Finder)->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
            preg_match_all('/(?<![\w.])route\([\'\"]([^\'\"]+)[\'\"]/', $file->getContents(), $matches);
            foreach ($matches[1] as $name) {
                $this->assertTrue(Route::has($name), $file->getRelativePathname().': '.$name);
                $count++;
            }
        }
        $this->assertGreaterThan(300, $count);
    }
}
