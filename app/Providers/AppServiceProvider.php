<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Evaluation;
use App\Models\Remark;
use App\Models\StudentDocument;
use App\Policies\AnnouncementPolicy;
use App\Policies\EvaluationPolicy;
use App\Policies\RemarkPolicy;
use App\Policies\StudentDocumentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Announcement::class, AnnouncementPolicy::class);
        Gate::policy(Evaluation::class, EvaluationPolicy::class);
        Gate::policy(Remark::class, RemarkPolicy::class);
        Gate::policy(StudentDocument::class, StudentDocumentPolicy::class);
    }
}
