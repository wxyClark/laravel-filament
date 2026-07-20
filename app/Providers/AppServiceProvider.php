<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Auth\Repositories\AdminRepositoryInterface;
use App\Domains\User\Repositories\CustomerRepositoryInterface;
use App\Infrastructure\Repositories\Eloquent\AdminRepository;
use App\Infrastructure\Repositories\Eloquent\CustomerRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminRepositoryInterface::class, AdminRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
    }

    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::shouldBeStrict(! $this->app->isProduction());
    }
}
