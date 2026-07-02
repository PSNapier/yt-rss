<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Headers for an Inertia partial reload that resolves the given deferred prop(s).
 *
 * The feed controllers wrap `videos` in Inertia::defer(), so it (and the RSS
 * fetch inside the closure) only resolves on the follow-up partial request the
 * client fires after the initial page shell loads.
 *
 * @param  array<int, string>  $only
 * @return array<string, string>
 */
function inertiaPartial(string $component, array $only = ['videos']): array
{
    $version = app(App\Http\Middleware\HandleInertiaRequests::class)
        ->version(Illuminate\Http\Request::create('/'));

    return array_filter([
        'X-Inertia' => 'true',
        'X-Inertia-Version' => $version,
        'X-Inertia-Partial-Component' => $component,
        'X-Inertia-Partial-Data' => implode(',', $only),
    ], fn ($value) => $value !== null);
}
