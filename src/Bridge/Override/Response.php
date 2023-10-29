<?php

namespace Monken\CIBurner\Bridge\Override;

use CodeIgniter\HTTP\Response as Ci4Response;

/**
 * Override CodeIgniter4 Response class
 * 
 */
class Response extends Ci4Response
{
    public function send()
    {
        if(defined('BURNER_DRIVER') === false){
            return parent::send();
        }
        if ($this->CSP->enabled()) {
            $this->CSP->finalize($this);
        } else {
            $this->body = str_replace(['{csp-style-nonce}', '{csp-script-nonce}'], '', $this->body ?? '');
        }
        $this->sendBody();
        return $this;
    }
}
