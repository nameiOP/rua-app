<?php

namespace rua\helpers;

use rua\traits\macroable;


/**
 * Class Optional
 * @package rua\helpers
 */
class Optional
{


    use macroable{
        __call as macroCall;
    }





    /**
     * The underlying object.
     *
     * @var mixed
     */
    protected $value;





    /**
     * Create a new optional instance.
     *
     * @param  mixed  $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }




    /**
     * Dynamically access a property on the underlying object.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if (is_object($this->value)) {
            return $this->value->{$key};
        }
    }





    /**
     * Dynamically pass a method to the underlying object.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (is_object($this->value)) {
            return $this->value->{$method}(...$parameters);
        }
    }




}
