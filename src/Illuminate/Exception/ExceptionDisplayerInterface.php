<?php namespace Illuminate\Exception;

use Exception;

interface ExceptionDisplayerInterface {

	/**
	 * Display the given exception to the user.
	 *
	 * @param $exception
	 */
	public function display($exception);

}
