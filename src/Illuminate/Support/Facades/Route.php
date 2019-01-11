<?php namespace Illuminate\Support\Facades;

/**
 * @method static \Illuminate\Routing\Route get(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \Illuminate\Routing\Route post(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \Illuminate\Routing\Route put(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \Illuminate\Routing\Route delete(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \Illuminate\Routing\Route patch(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \Illuminate\Routing\Route options(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \Illuminate\Routing\Route any(string $uri, \Closure|array|string|callable|null $action = null)
 * @method static \Illuminate\Routing\Route match(array|string $methods, string $uri, \Closure|array|string|callable|null $action = null)
 *
 * @see \Illuminate\Routing\Router
 */
class Route extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'router'; }

}
