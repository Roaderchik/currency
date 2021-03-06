<?php namespace Roaderchik\Currency;

use DB;
use Cache;
use Illuminate\Support\Facades\Request;
use Cookie;
use Session;

class Currency {

	/**
	 * Laravel application
	 *
	 * @var \Illuminate\Foundation\Application
	 */
	public $app;

	/**
	 * Default currency
	 *
	 * @var string
	 */
	protected $code;

	/**
	 * All currencies
	 *
	 * @var array
	 */
	protected $currencies = [];

	/**
	 * Create a new instance.
	 *
	 * @param \Illuminate\Foundation\Application $app
	 * @return void
	 */
	public function __construct($app)
	{
		$this->app = $app;

		// Initialize Currencies
		$this->setCacheCurrencies();

		// Check for a user defined currency
		if (Request::get('currency') && array_key_exists(Request::get('currency'), $this->currencies))
		{
			$this->setCurrency(Input::get('currency'));
		}
		elseif (Session::get('currency') && array_key_exists(Session::get('currency'), $this->currencies))
		{
			$this->setCurrency(Session::get('currency'));
		}
		elseif (Cookie::get('currency') && array_key_exists(Cookie::get('currency'), $this->currencies))
		{
			$this->setCurrency(Cookie::get('currency'));
		}
		else
		{
			$this->setCurrency($this->app['config']['currency.default']);
		}
	}

	public function format($number, $currency = null, $symbol_style = '%symbol%', $inverse = false, $rounding_type = '', $precision = null, $decimal_place = null)
	{
		if ($currency && $this->hasCurrency($currency))
		{
			$symbol_left    = $this->currencies[$currency]['symbol_left'];
			$symbol_right   = $this->currencies[$currency]['symbol_right'];
			if (is_null($decimal_place))
			{
				$decimal_place  = $this->currencies[$currency]['decimal_place'];
			}
			$decimal_point  = $this->currencies[$currency]['decimal_point'];
			$thousand_point = $this->currencies[$currency]['thousand_point'];
		}
		else
		{
			$symbol_left    = $this->currencies[$this->code]['symbol_left'];
			$symbol_right   = $this->currencies[$this->code]['symbol_right'];
			if (is_null($decimal_place))
			{
				$decimal_place  = $this->currencies[$this->code]['decimal_place'];
			}
			$decimal_point  = $this->currencies[$this->code]['decimal_point'];
			$thousand_point = $this->currencies[$this->code]['thousand_point'];

			$currency = $this->code;
		}

		if ($value = $this->currencies[$currency]['value'])
		{
			if ($inverse)
			{
				$value = $number * (1 / $value);
			}
			else
			{
				$value = $number * $value;
			}
		}
		else
		{
			$value = $number;
		}

		$string = '';

		if ($symbol_left)
		{
			$string .= str_replace('%symbol%', $symbol_left, $symbol_style);

			if ($this->app['config']['currency.use_space'])
			{
				$string .= ' ';
			}
		}

		switch ($rounding_type)
		{
			case 'ceil':
			case 'ceiling':
				if ($precision != null)
				{
					$multiplier = pow(10, -(int) $precision);
				}
				else
				{
					$multiplier = pow(10, -(int) $decimal_place);
				}

				$string .= number_format(ceil($value / $multiplier) * $multiplier, (int) $decimal_place, $decimal_point, $thousand_point);
				break;

			case 'floor':
				if ($precision != null)
				{
					$multiplier = pow(10, -(int) $precision);
				}
				else
				{
					$multiplier = pow(10, -(int) $decimal_place);
				}

				$string .= number_format(floor($value / $multiplier) * $multiplier, (int) $decimal_place, $decimal_point, $thousand_point);
				break;

			default:
				if ($precision == null)
				{
					$precision = (int) $decimal_place;
				}

				$string .= number_format(round($value, (int) $precision), (int) $decimal_place, $decimal_point, $thousand_point);
				break;
		}

		if ($symbol_right)
		{
			if ($this->app['config']['currency.use_space'])
			{
				$string .= ' ';
			}

			$string .= str_replace('%symbol%', $symbol_right, $symbol_style);
		}

		return $string;
	}

	public function normalize($number, $dec = false)
	{
		$value = $this->currencies[$this->code]['value'];

		if ($value)
		{
			$value = $number * $value;
		}
		else
		{
			$value = $number;
		}

		if ( ! $dec)
		{
			$dec = $this->currencies[$this->code]['decimal_place'];
		}

		return number_format(round($value, (int) $dec), (int) $dec, '.', '');
	}

	/**
	 * Currency round function
	 *
	 * @param $number
	 * @param int $decimal_place
	 * @param null $currency
	 * @return mixed
	 */
	public function rounded($number, $decimal_place = 0, $currency = null)
	{
		return $this->style( $number, $currency, '%symbol%', false, '', null, $decimal_place );
	}

	/**
	 * Get currency symbol
	 *
	 * @param bool|false $right
	 * @return mixed
	 */
	public function getCurrencySymbol($right = false)
	{
		if ($right)
		{
			return $this->currencies[$this->code]['symbol_right'];
		}

		return $this->currencies[$this->code]['symbol_left'];
	}

	/**
	 * Checks whether this currency is available in the database
	 *
	 * @param $currency
	 * @return bool
	 */
	public function hasCurrency($currency)
	{
		return isset($this->currencies[$currency]);
	}

	/**
	 * Set base currency in session
	 *
	 * @param $currency
	 */
	public function setCurrency($currency)
	{
		$this->code = $currency;

		if (Session::get('currency') != $currency)
		{
			Session::put('currency', $currency);
		}

		if (Cookie::get('currency') != $currency)
		{
			Cookie::make('currency', $currency, time() + 60 * 60 * 24 * 30);
		}
	}

	/**
	 * Return the current currency code
	 *
	 * @return string
	 */
	public function getCurrencyCode()
	{
		return $this->code;
	}

	/**
	 * Return the current currency if the
	 * one supplied is not valid.
	 *
	 * @return array
	 */
	public function getCurrency($currency = '')
	{
		if ($currency && $this->hasCurrency($currency))
		{
			return $this->currencies[$currency];
		}
		else
		{
			return $this->currencies[$this->code];
		}
	}

	/**
	 * Convert value from one currency to another currency
	 *
	 * @param $number
	 * @param $fromCurrencyCode
	 * @param $toCurrencyCode
	 * @return float
	 */
	public function convert($number, $fromCurrencyCode, $toCurrencyCode)
	{
		$fromCurrency = $this->getCurrency($fromCurrencyCode);
		$toCurrency = $this->getCurrency($toCurrencyCode);

		return round($number / $fromCurrency['value'] * $toCurrency['value'], 2);
	}

	/**
	 * Initialize Currencies.
	 *
	 * @return void
	 */
	public function setCacheCurrencies()
	{
		$db = $this->app['db'];

		$this->currencies = Cache::rememberForever('currency', function() use($db)
		{
			$cache      = [];
			$table_name = $this->app['config']['currency.table_name'];

			foreach ($db->table($table_name)->get() as $currency)
			{
				$cache[$currency->code] = [
					'id'             => $currency->id,
					'title'          => $currency->title,
					'symbol_left'    => $currency->symbol_left,
					'symbol_right'   => $currency->symbol_right,
					'decimal_place'  => $currency->decimal_place,
					'value'          => $currency->value,
					'decimal_point'  => $currency->decimal_point,
					'thousand_point' => $currency->thousand_point,
					'code'           => $currency->code,
				];
			}

			return $cache;
		});
	}
}
