<?php namespace Koizoinno\Worldpay;

use Illuminate\Support\ServiceProvider;

class WorldpayServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['worldpay_client'] = new WorldPayClient();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('worldpay_client');
	}

}
