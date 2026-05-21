<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\Rag\RagRetriever;
use App\Services\Scoring\LeadScorer;
use App\Services\Scoring\Rules\AdditionalDataCompletenessRule;
use App\Services\Scoring\Rules\AiScoringRule;
use App\Services\Scoring\Rules\BudgetRule;
use App\Services\Scoring\Rules\DataCompletenessRule;
use App\Services\Scoring\Rules\EmailDomainRule;
use App\Services\Scoring\Rules\IranPhoneRule;
use App\Services\Scoring\Rules\SourceRule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LeadScorer::class, function () {
            $scorer = new LeadScorer;

            $scorer->addRule(new BudgetRule);
            $scorer->addRule(new SourceRule);
            $scorer->addRule(new DataCompletenessRule);
            $scorer->addRule(new EmailDomainRule);
            $scorer->addRule(new AdditionalDataCompletenessRule);
            $scorer->addRule(new IranPhoneRule);
            $scorer->addRule(new AiScoringRule(new RagRetriever));

            return $scorer;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->mergeAiSettings();

        $this->configureDefaults();
    }

    /**
     * Merge AI settings from the database into config.
     */
    protected function mergeAiSettings(): void
    {
        try {
            $key = Setting::getValue('ai_api_key');
            $provider = Setting::getValue('ai_provider', 'openai');
            $model = Setting::getValue('ai_model');

            if ($key) {
                config(["ai.providers.{$provider}.key" => $key]);
            }

            if ($model) {
                config(["ai.providers.{$provider}.model" => $model]);
            }
        } catch (\Exception $e) {
            // Settings table may not exist yet (e.g., fresh install)
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
