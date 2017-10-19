<?php

namespace LaravelZero\Framework\Bootstrappers;

use Illuminate\Support\ServiceProvider;
use LaravelZero\Framework\Providers;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Events\EventServiceProvider;
use LaravelZero\Framework\Commands\Component;
use NunoMaduro\LaravelDesktopNotifier\LaravelDesktopNotifierServiceProvider;

/**
 * This is the Zero Framework Bootstrapper ServiceProviders class.
 *
 * @author Nuno Maduro <enunomaduro@gmail.com>
 */
class ServiceProviders extends Bootstrapper
{
    /**
     * The application's core providers.
     *
     * @var string[]
     */
    protected $providers = [
        Providers\ErrorHandler\ServiceProvider::class,
        EventServiceProvider::class,
        CacheServiceProvider::class,
        Providers\Composer\ServiceProvider::class,
        Providers\Scheduler\ServiceProvider::class,
        LaravelDesktopNotifierServiceProvider::class,
    ];

    /**
     * The application's core components.
     *
     * @var string[]
     */
    protected $components = [
        Component\Illuminate\Database\ComponentProvider::class,
        Component\Illuminate\Filesystem\ComponentProvider::class,
    ];

    /**
     * The application core aliases.
     *
     * @var array
     */
    protected $aliases = [
        'app' => [\Illuminate\Contracts\Container\Container::class],
        'events' => [\Illuminate\Events\Dispatcher::class, \Illuminate\Contracts\Events\Dispatcher::class],
        'config' => [\Illuminate\Config\Repository::class, \Illuminate\Contracts\Config\Repository::class],
        'cache' => [\Illuminate\Cache\CacheManager::class, \Illuminate\Contracts\Cache\Factory::class],
        'cache.store' => [\Illuminate\Cache\Repository::class, \Illuminate\Contracts\Cache\Repository::class],
    ];

    /**
     * {@inheritdoc}
     */
    public function bootstrap(): void
    {
        collect($this->providers)
            ->merge($this->components)
            ->merge(
                $this->container->make('config')
                    ->get('app.providers')
            )
            ->each(
                function ($serviceProviderClass) {
                    $this->call($serviceProviderClass, 'register');
                }
            )
            ->each(
                function ($serviceProviderClass) {
                    $this->call($serviceProviderClass, 'boot');
                }
            );

        foreach ($this->aliases as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->container->alias($key, $alias);
            }
        }
    }

    /**
     * Creates a new instance of the service provider an
     * calls the provided method.
     *
     * @param string $serviceProviderClass
     * @param string $method
     */
    private function call(string $serviceProviderClass, string $method): void
    {
        $provider = new $serviceProviderClass($this->container);
        if (method_exists($provider, $method)) {
            $this->container->call([$provider, $method]);
        }
    }
}
