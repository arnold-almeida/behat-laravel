<?php namespace GuilhermeGuitte\BehatLaravel;

use Illuminate\Console\Command;
use Illuminate\Config\Repository;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RunBehatLaravelCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'behat:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the all acceptance tests';

    /**
     * Illuminate application instance.
     *
     * @var Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Config from behat.yml
     * @var array
     */
    protected $config;

    /**
     * Package config from guilhermeguitte\behat-laravel\src\config\config.php
     * @var array
     */
    protected $environmentConfig;

    /**
     * Create a new BehatLaravel command instance.
     *
     * @param  GuilhermeGuitte\BehatLaravel $behat
     * @return void
     */
    public function __construct($config, $environmentConfig)
    {
        $this->environmentConfig = $environmentConfig;
        $this->config = $config;


        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        passthru('clear');


        $this->line('');

        $this->comment("Running acceptance tests... \n\n");

        $this->setDefaultEnvironment();

        $this->checkEnvironment();

        $input = array();
        $input[] = '';

        $options = array('format', 'no-snippets','tags', 'out','profile', 'name');

        foreach ($options as $option) {
            if ( ($format = $this->input->getOption($option) ) ) {
                $input[] = "--$option=".$format;
            }
        }

        $profile = $this->option('profile');

        if (!empty($profile)) {
            $profile_config = $this->loadConfig($profile);
        } else {
            $profile_config = $this->loadConfig('default');
        }

        $input[] = $profile_config['paths']['features'].'/'.$this->input->getArgument('feature');

        // Running with output color
        $app = new \Behat\Behat\Console\BehatApplication('DEV');
        $app->run(new \Symfony\Component\Console\Input\ArgvInput(
            $input
        ));
    }

    /**
     * If no environment has been provided use the default environment
     * Typically ("testing") as defined in our package config
     */
    public function setDefaultEnvironment()
    {
        $env = $this->option('env');

        if (!$env) {
            $this->input->setOption('env', $this->environmentConfig['default']);
        }
    }

    /**
     * Detect env and ensure that we dont accidentally run our tests on a production database
     */
    public function checkEnvironment()
    {
        $env = $this->option('env');

        if (in_array($env, $this->environmentConfig['blacklisted'])) {
            if (!$this->confirm("Are you sure you wish to run tests on your [{$env}] env ? [y:N]", false)) {
                $this->info("Cancelled : Running tests in [{$env}] is cray cray!");
                exit;
            }
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('feature', InputArgument::OPTIONAL, 'Runs tests in the specified folder or file only.'),
        );
    }

    protected function getOptions()
    {
        return array(
            array('format', 'f', InputOption::VALUE_REQUIRED, 'Choose a formatter from <caption>pretty</caption> (default), progress, html, junit, failed, snippets.'),
            array('tags', NULL, InputOption::VALUE_REQUIRED, 'Only execute the features or scenarios with tags matching the tag filter expression.'),
            array('no-snippets', NULL, InputOption::VALUE_NONE, 'Don\'t print snippets for unmatched steps'),
            array('profile', 'p', InputOption::VALUE_REQUIRED, 'Specify a profile from behat.yml'),
            array('out', 'o', InputOption::VALUE_REQUIRED, 'Choose a formatter from <caption>pretty</caption> (default), progress, html, junit, failed, snippets.'),
            array('name', NULL, InputOption::VALUE_REQUIRED, 'Only execute the feature elements which match part of the given name or regex.'),
        );
    }

    /**
     * Load the profile specific config
     *
     * @param  string     $profile
     * @return array|NULL
     */
    protected function loadConfig($profile)
    {
        if (!empty($this->config)) {
            return (isset($this->config[$profile]))? $this->config[$profile] : null;
        }

        return null;
    }
}
