<?php namespace Illuminate\Support;

use Opis\Closure\SerializableClosure as SuperClosure;

/**
 * Extends SuperClosure for backwards compatibility.
 */
class SerializableClosure extends SuperClosure {

	/**
	 * The variables that were "used" or imported from the parent scope
	 *
	 * @var array
	 */
	protected $variables;

	/**
	 * Returns the code of the closure being serialized
	 *
	 * @return string
	 */
	public function getCode()
	{
		$this->determineCodeAndVariables();

		return $this->code;
	}

	/**
	 * Returns the "used" variables of the closure being serialized
	 *
	 * @return array
	 */
	public function getVariables()
	{
		$this->determineCodeAndVariables();

		return $this->variables;
	}

	/**
	 * Uses the serialize method directly to lazily fetch the code and variables if needed
	 */
	protected function determineCodeAndVariables()
	{
		if ( ! $this->code)
		{
            $ref = $this->getReflector();
            $this->variables = $ref->getStaticVariables();
            $this->code = $ref->getCode();
		}
	}

}
