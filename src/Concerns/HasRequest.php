<?php

namespace Saidqb\CodeigniterSupport\Concerns;

use Saidqb\CorePhp\Lib\Check;


trait HasRequest
{
    protected function input($key = null, $default = null)
    {
        $reqGetPost = $this->request->getGet();

        if ($this->request->getHeaderLine('Content-Type') == 'application/json') {
            $reqJson = $this->request->getJSON(true);
        }

        if (empty($reqJson)) {
            $reqJson = [];
        }

        $req = [...$reqGetPost, ...$reqJson];

        if ($key) {
            return Check::issetVal($req, $key, $default);
        }

        return $req;
    }
}
