<?php

namespace Scaleplan\Sberbank\DTO;

use Scaleplan\DTO\DTO;

/**
 * Class ErrorStructure
 *
 * @package Scaleplan\Sberbank
 */
class ErrorDTO extends DTO
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var array|null
     */
    private $response;

    /**
     * @var int
     */
    private $httpStatusCode;

    /**
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url) : void
    {
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function getParams() : array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params) : void
    {
        $this->params = $params;
    }

    /**
     * @return array|null
     */
    public function getResponse() : ?array
    {
        return $this->response;
    }

    /**
     * @param array|null $response
     */
    public function setResponse(?array $response) : void
    {
        $this->response = $response;
    }

    /**
     * @return int
     */
    public function getHttpStatusCode() : int
    {
        return $this->httpStatusCode;
    }

    /**
     * @param int $httpStatusCode
     */
    public function setHttpStatusCode(int $httpStatusCode) : void
    {
        $this->httpStatusCode = $httpStatusCode;
    }
}
