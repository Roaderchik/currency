<?php namespace roaderchik\Currency\Commands;

use Illuminate\Console\Command;

use Cache;

class CurrencyCleanupCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'currency:cleanup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Cleanup currency cache';

	/**
	 * Application instance
	 *
	 * @var Illuminate\Foundation\Application
	 */
	protected $app;

	/**
	 * Create a handle.
	 *
	 * @return void
	 */
	public function handle()
    {
            return $this->fire();
	} 
	
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		Cache::forget('currency');

		$this->info('Currency cache cleaned.');
	}
}