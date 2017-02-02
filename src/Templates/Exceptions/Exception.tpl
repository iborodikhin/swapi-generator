<?php
namespace %api_namespace%\Exceptions;

/**
 * Base API exception
 */
abstract class Exception extends \Exception
{
    /**
     * Additional data for exception.
     *
     * @var array
     */
    protected $additionalData = [];

    /**
     * Set additional data
     *
     * @param  array $data
     * @return \%api_namespace%\Exceptions\Exception
     */
    public function setAdditionalData(array $data = [])
    {
        $this->additionalData = $data;

        return $this;
    }

    /**
     * Get additional data
     *
     * @return array
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }
}
