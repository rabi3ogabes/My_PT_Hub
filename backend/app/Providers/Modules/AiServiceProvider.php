<?php

namespace App\Providers\Modules;

use App\Domain\Ai\Contracts\AiProvider;
use App\Domain\Ai\Providers\AnthropicProvider;
use App\Domain\Ai\Providers\FakeProvider;
use App\Domain\Ai\Providers\OpenAiProvider;
use App\Domain\Ai\Services\SafetyGuard;
use App\Domain\Ai\Support\PromptLibrary;
use App\Domain\Ai\Support\SchemaValidator;
use Illuminate\Support\ServiceProvider;

/** Binds the AI driver selected by services.ai.driver (anthropic|openai|fake). */
class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FakeProvider::class);
        $this->app->singleton(SafetyGuard::class);
        $this->app->singleton(PromptLibrary::class);
        $this->app->singleton(SchemaValidator::class);

        $this->app->bind(OpenAiProvider::class, fn ($app) => new OpenAiProvider(
            config('services.ai.openai_key'),
            (string) config('services.ai.openai_model', 'gpt-4.1-mini'),
            $app->make(SchemaValidator::class),
        ));

        $this->app->bind(AnthropicProvider::class, fn ($app) => new AnthropicProvider(
            config('services.ai.anthropic_key'),
            (string) config('services.ai.anthropic_model', 'claude-sonnet-5-5'),
            (string) (config('services.ai.anthropic_vision_model') ?: config('services.ai.anthropic_model', 'claude-sonnet-5-5')),
            $app->make(SchemaValidator::class),
            config('services.ai.openai_key') ? $app->make(OpenAiProvider::class) : null,
        ));

        $this->app->bind(AiProvider::class, fn ($app) => match (config('services.ai.driver', 'fake')) {
            'anthropic' => $app->make(AnthropicProvider::class),
            'openai' => $app->make(OpenAiProvider::class),
            default => $app->make(FakeProvider::class),
        });
    }
}
