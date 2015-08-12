<?php

use Illuminate\Database\Migrations\Migration;

class CreateCurrencyTable extends Migration {

	protected $table_name;

	public function __construct()
	{
		$this->table_name = Config::get('currency.table_name');
	}

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Create the currency table
		Schema::create($this->table_name, function($table)
		{
			$table->increments('id')->unsigned();
			$table->string('title', 255);
			$table->string('symbol_left', 12);
			$table->string('symbol_right', 12);
			$table->string('code', 3);
			$table->integer('decimal_place');
			$table->double('value', 15, 8);
			$table->string('decimal_point', 3);
			$table->string('thousand_point', 3);
			$table->integer('status');
			$table->timestamps();
		});

		$currencies = array(
			array(
				'id' => 1,
				'title' => 'U.S. Dollar',
				'symbol_left' => '$',
				'symbol_right' => '',
				'code' => 'USD',
				'decimal_place' => 2,
				'value' => 1.00000000,
				'decimal_point' => '.',
				'thousand_point' => ',',
				'status' => 1,
				'created_at' => '2013-11-29 19:51:38',
				'updated_at' => '2013-11-29 19:51:38',
			),
			array(
				'id' => 2,
				'title' => 'Euro',
				'symbol_left' => '€',
				'symbol_right' => '',
				'code' => 'EUR',
				'decimal_place' => 2,
				'value' => 0.74970001,
				'decimal_point' => '.',
				'thousand_point' => ',',
				'status' => 1,
				'created_at' => '2013-11-29 19:51:38',
				'updated_at' => '2013-11-29 19:51:38',
			),
			array(
				'id' => 3,
				'title' => 'Pound Sterling',
				'symbol_left' => '£',
				'symbol_right' => '',
				'code' => 'GBP',
				'decimal_place' => 2,
				'value' => 0.62220001,
				'decimal_point' => '.',
				'thousand_point' => ',',
				'status' => 1,
				'created_at' => '2013-11-29 19:51:38',
				'updated_at' => '2013-11-29 19:51:38',
			),
			array(
				'id' => 4,
				'title' => 'Russian ruble',
				'symbol_left' => 'RUB',
				'symbol_right' => '',
				'code' => 'RUB',
				'decimal_place' => 2,
				'value' => 64.02600000,
				'decimal_point' => '.',
				'thousand_point' => ',',
				'status' => 1,
				'created_at' => '2013-11-29 19:51:38',
				'updated_at' => '2013-11-29 19:51:38',
			)
		);

		DB::table($this->table_name)->insert($currencies);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Delete the currency table
		Schema::drop($this->table_name);
	}

}
