<?php

namespace App\Providers;

use App\Auth\HashedRememberUserProvider;
use App\Models\ArticlePayment;
use App\Models\Copyedit;
use App\Models\CopyeditApproval;
use App\Models\EditorAssignment;
use App\Models\Galley;
use App\Models\GalleyApproval;
use App\Models\Issue;
use App\Models\Journal;
use App\Models\LayoutAssignment;
use App\Models\ProductionEscalation;
use App\Models\ProofAssignment;
use App\Models\ProofreadingNote;
use App\Models\Review;
use App\Models\ReviewAssignment;
use App\Models\ReviewExtension;
use App\Models\ReviewReminder;
use App\Models\ReviewRound;
use App\Models\Revision;
use App\Models\Section;
use App\Models\Submission;
use App\Services\PortalAccess;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Auth::provider('sirida', fn ($app, $config) => new HashedRememberUserProvider($app['hash'], $config['model']));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([ReviewExtension::class, ReviewReminder::class, Review::class, ProofreadingNote::class, GalleyApproval::class, CopyeditApproval::class, Submission::class, Journal::class, Section::class, Issue::class, Copyedit::class, Galley::class, LayoutAssignment::class, ReviewRound::class, ReviewAssignment::class, ProofAssignment::class, EditorAssignment::class, Revision::class, ArticlePayment::class, ProductionEscalation::class] as $model) {
            $model::addGlobalScope('portal_access', fn ($query) => PortalAccess::apply($query->getModel(), $query));
        }

        RateLimiter::for('login', fn (Request $request) => [Limit::perMinute(30)->by(hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')))]);

        RateLimiter::for('identity', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->ip()),
                Limit::perHour(100)->by($request->ip()),
            ];
        });
    }
}
