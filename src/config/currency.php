<?php

return array(

	/*
	 |--------------------------------------------------------------------------
	 | Default currency
	 |--------------------------------------------------------------------------
	 */

	'default' => 'USD',

	/*
	 |--------------------------------------------------------------------------
	 | Currency that need to be updated
	 |--------------------------------------------------------------------------
	 */

	'needed' => ['USD', 'EUR', 'GBP', 'RUB', 'BYR'],

	/*
	 |--------------------------------------------------------------------------
	 | API Key for OpenExchangeRates.org
	 |--------------------------------------------------------------------------
	 |
	 | Only required if you with to use the Open Exchange Rates api. You can
	 | always just use Yahoo, the current default.
	 |
	 */

	'api_key' => '',

	/*
	 |--------------------------------------------------------------------------
	 | Add a single space between value and currency symbol
	 |--------------------------------------------------------------------------
	 */

	'use_space' => false,

	/*
	 |--------------------------------------------------------------------------
	 | Currencies table name
	 |--------------------------------------------------------------------------
	 */

	'table_name' => 'currency',

	/*
	 |--------------------------------------------------------------------------
	 | Config for http://www.cbr.ru
	 |--------------------------------------------------------------------------
	 */

	'cbr' => [
		'url' => 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=',
		'description' => 'Updating currency exchange rates from www.cbr.ru...',
		'date_format' => 'd/m/Y',
		'code' => 'RUB',
		'currency' => 'Valute',
		'nominal' => 'Nominal',
		'value' => 'Value'
	],

	/*
	 |--------------------------------------------------------------------------
	 | Config for http://www.nbrb.by
	 |--------------------------------------------------------------------------
	 */

	'nbrb' => [
		'url' => 'http://www.nbrb.by/Services/XmlExRates.aspx?ondate=',
		'description' => 'Updating currency exchange rates from www.nbrb.by...',
		'date_format' => 'd/m/Y',
		'code' => 'BYR',
		'currency' => 'Currency',
		'nominal' => 'Scale',
		'value' => 'Rate'
	],

);