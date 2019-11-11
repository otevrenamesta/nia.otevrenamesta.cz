<?php

namespace App\Saml;

use SAML2\Configuration\ServiceProvider;

class NiaServiceProvider extends ServiceProvider
{
    const LOA_LOW = 'http://eidas.europa.eu/LoA/low';
    const LOA_SUBSTANTIAL = 'http://eidas.europa.eu/LoA/substantial';
    const LOA_HIGH = 'http://eidas.europa.eu/LoA/high';

    public static $AssertionConsumerServiceURL = 'https://nia.otevrenamesta.cz/ExternalLogin';

    public function __construct()
    {
        parent::__construct([]);
    }

    public function getPrivateKey(string $name, bool $required = null)
    {
        return file_get_contents(CONFIG . 'private.key');
    }
}