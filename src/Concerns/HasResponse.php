<?php

namespace Saidqb\CodeigniterSupport\Concerns;

use Saidqb\CorePhp\Response;
use Saidqb\CorePhp\ResponseCode;

trait HasResponse
{
    protected $res;
    
    protected function initResponse()
    {
        $this->res = new Response();

        return $this->res;
    }

    protected function response($data, $code = ResponseCode::HTTP_OK, $message = ResponseCode::HTTP_OK_MESSAGE, $errorCode = 0)
    {
        $this->res->response($data, $code, $message, $errorCode)->send();
    }
}
