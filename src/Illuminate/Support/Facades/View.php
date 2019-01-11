<?php namespace Illuminate\Support\Facades;

/**
 * @method static \Illuminate\View\View make(string $view, array $data = [], array $mergeData = [])
 *
 * @see \Illuminate\View\Factory
 */
class View extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'view'; }

}
