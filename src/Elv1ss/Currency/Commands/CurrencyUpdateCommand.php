<?php namespace elv1ss\Currency\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

use DB;
use Cache;
use DateTime;

class CurrencyUpdateCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'currency:update';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update exchange rates from Yahoo';

	/**
	 * Application instance
	 *
	 * @var Illuminate\Foundation\Application
	 */
	protected $app;

	/**
	 * Currencies table name
	 *
	 * @var string
	 */
	protected $table_name;

	/**
	 * Create a new command instance.
	 *
	 * @param Illuminate\Foundation\Application $app
	 * @return void
	 */
	public function __construct($app)
	{
		$this->app        = $app;
		$this->table_name = $app['config']['currency.table_name'];

		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		// Get Settings
		$defaultCurrency = $this->app['config']['currency.default'];

		if ($this->input->getOption('openexchangerates'))
		{
			if ( ! $api = $this->app['config']['currency.api_key'])
			{
				$this->error('An API key is needed from OpenExchangeRates.org to continue.');
				return;
			}

			// Get rates
			$this->updateFromOpenExchangeRates($defaultCurrency, $api);
		}
		else if ($this->input->getOption('cbr'))
		{
			$this->updateFromCBRRates($defaultCurrency);
		}
		else
		{
			// Get rates
			$this->updateFromYahoo($defaultCurrency);
		}
	}

	private function updateFromYahoo($defaultCurrency)
	{
		$this->info('Updating currency exchange rates from Finance Yahoo...');

		$data = [];

		// Get all currencies
		foreach($this->app['db']->table($this->table_name)->get() AS $currency)
		{
			$data[] = "{$defaultCurrency}{$currency->code}=X";
		}

		// Ask Yahoo for exchange rate
		if ($data)
		{
			$content = $this->request('http://download.finance.yahoo.com/d/quotes.csv?s=' . implode(',', $data) . '&f=sl1&e=.csv');

			$lines = explode("\n", trim($content));

			// Update each rate
			foreach ($lines as $line)
			{
				$code = substr($line, 4, 3);
				$value = substr($line, 11, 6);

				if ($value)
				{
					$this->app['db']->table($this->table_name)
						->where('code', $code)
						->update([
							'value'      => $value,
							'updated_at' => new DateTime('now'),
						]);
				}
			}

			Cache::forget('currency');
		}

		$this->info('Update!');
	}

	private function updateFromOpenExchangeRates($defaultCurrency, $api)
	{
		$this->info('Updating currency exchange rates from OpenExchangeRates.org...');

		// Make request
		$content = json_decode($this->request("http://openexchangerates.org/api/latest.json?base={$defaultCurrency}&app_id={$api}"));

		// Error getting content?
		if (isset($content->error))
		{
			$this->error($content->description);
			return;
		}

		// Parse timestamp for DB
		$timestamp = new DateTime(strtotime($content->timestamp));

		// Update each rate
		foreach ($content->rates as $code=>$value)
		{
			$this->app['db']->table($this->table_name)
				->where('code', $code)
				->update([
					'value'      => $value,
					'updated_at' => $timestamp
				]);
		}

		Cache::forget('currency');

		$this->info('Update!');
	}

	private function updateFromCBRRates($defaultCurrency)
	{
		$this->info('Updating currency exchange rates from www.cbr.ru...');

		$xml = $this->request('http://www.cbr.ru/scripts/XML_daily.asp?date_req=' . date('d/m/Y'));
		$currencyRates = new \SimpleXMLElement($xml);


		$default = 1;
		$rates = array();
		$needed = $this->app['config']['currency.needed'];
		foreach($currencyRates->Valute as $data)
		{
			if (in_array($data->CharCode, $needed))
			{
				if ($data->CharCode == $defaultCurrency)
				{
					$default = str_replace(",", ".", $data->Value) / (float)$data->Nominal;
					$rates[] = array(
						'code' => $defaultCurrency,
						'value' => 1
					);
				}
				else
				{
					$rates[] = array(
						'code' => $data->CharCode,
						'value' => (str_replace(",", ".", $data->Value) / (float)$data->Nominal)
					);
				}
			}
		}

		$rates[] = array(
			'code' => 'RUB',
			'value' => $default
		);

		foreach($rates as $rate)
		{
			$this->app['db']->table($this->table_name)
				->where('code', $rate['code'])
				->update(['value' => in_array($rate['code'], ['RUB', $defaultCurrency]) ? $rate['value'] : $default / $rate['value']]);
		}

		Cache::forget('currency');

		$this->info('Update!');
	}

	private function request($url)
	{
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
		curl_setopt($ch, CURLOPT_MAXCONNECTS, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['openexchangerates', 'o', InputOption::VALUE_NONE, 'Get rates from OpenExchangeRates.org'],
			['cbr', 'c', InputOption::VALUE_NONE, 'Get rates from cbr.ru']
		];
	}
}
