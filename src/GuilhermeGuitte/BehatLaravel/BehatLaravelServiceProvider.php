<?php namespace GuilhermeGuitte\BehatLaravel;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Yaml\Yaml;

class BehatLaravelServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the commands.
     *
     * @return void
     */
    public function register()
    {
        // Register package namespace
        $this->package('guilhermeguitte/behat-laravel');

        $environmentConfig = $this->app['config']->get('behat-laravel::environments');

        $this->app['command.behat.install'] = $this->app->share(function($app)
        {
            return new BehatLaravelCommand();
        });

        $this->commands('command.behat.install');

        $this->app['command.behat.run'] = $this->app->share(function($app) use($environmentConfig)
        {
            $config = Yaml::parse('app/../behat.yml');
            return new RunBehatLaravelCommand($config, $environmentConfig);
        });

        $this->commands('command.behat.run');

        $this->app['command.behat.feature'] = $this->app->share(function($app)
        {
            $config = Yaml::parse('app/../behat.yml');
            return new FeatureBehatLaravelCommand($config);
        });

        $this->commands('command.behat.feature');

        $this->app['command.behat.generate_doc'] = $this->app->share(function($app)
        {
            $config = Yaml::parse('app/../behat.yml');
            return new DocumentationCommand($config);
        });

        $this->commands('command.behat.generate_doc');
    }

}
