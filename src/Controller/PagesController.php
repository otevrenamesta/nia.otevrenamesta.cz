<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller;

use Exception;
use OneLogin\Saml2\IdPMetadataParser;
use OneLogin\Saml2\Metadata;

class PagesController extends AppController
{

    private $metadata_url = "https://tnia.eidentita.cz/FPSTS/FederationMetadata/2007-06/FederationMetadata.xml";
    private $example_configuration = [
        'strict' => true,
        'debug' => true,
        'baseurl' => 'https://nia.otevrenamesta.cz/',
        'sp' => [
            'entityId' => 'https://nia.otevrenamesta.cz/',
            'assertionConsumerService' => [
                'url' => 'https://nia.otevrenamesta.cz/ExternalLogin',
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'
            ],
            "attributeConsumingService" => [
                "serviceName" => "Otevřená Města",
                "serviceDescription" => "Otevřená Města",
                "requestedAttributes" => [
                    [
                        "name" => "http://eidas.europa.eu/attributes/naturalperson/CurrentFamilyName",
                        "isRequired" => false,
                        "nameFormat" => "urn:oasis:names:tc:SAML:2.0:attrname-format:uri",
                        "friendlyName" => "CurrentFamilyName",
                        "attributeValue" => ""
                    ],
                    [
                        "name" => "http://eidas.europa.eu/attributes/naturalperson/PersonIdentifier",
                        "isRequired" => false,
                        "nameFormat" => "urn:oasis:names:tc:SAML:2.0:attrname-format:uri",
                        "friendlyName" => "PersonIdentifier",
                        "attributeValue" => ""
                    ],
                    [
                        "name" => "http://eidas.europa.eu/attributes/naturalperson/CurrentGivenName",
                        "isRequired" => false,
                        "nameFormat" => "urn:oasis:names:tc:SAML:2.0:attrname-format:uri",
                        "friendlyName" => "CurrentGivenName",
                        "attributeValue" => ""
                    ],
                    [
                        "name" => "http://eidas.europa.eu/attributes/naturalperson/CurrentFamilyName",
                        "isRequired" => false,
                        "nameFormat" => "urn:oasis:names:tc:SAML:2.0:attrname-format:uri",
                        "friendlyName" => "CurrentFamilyName",
                        "attributeValue" => ""
                    ],
                    [
                        "name" => "http://eidas.europa.eu/attributes/naturalperson/CurrentAddress",
                        "isRequired" => false,
                        "nameFormat" => "urn:oasis:names:tc:SAML:2.0:attrname-format:uri",
                        "friendlyName" => "CurrentAddress",
                        "attributeValue" => ""
                    ],
                    [
                        "name" => "http://eidas.europa.eu/attributes/naturalperson/DateOfBirth",
                        "isRequired" => false,
                        "nameFormat" => "urn:oasis:names:tc:SAML:2.0:attrname-format:uri",
                        "friendlyName" => "DateOfBirth",
                        "attributeValue" => ""
                    ],
                ]
            ],
            'singleLogoutService' => [
                // URL Location where the <Response> from the IdP will be returned
                'url' => 'https://nia.otevrenamesta.cz/ExternalLogout',
                // SAML protocol binding to be used when returning the <Response>
                // message.  Onelogin Toolkit supports for this endpoint the
                // HTTP-Redirect binding only
                'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            ],
            'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:entity',
            'x509cert' => 'MIIDdDCCAlygAwIBAgIRAP9sUbldL412M4EpX2fV5PwwDQYJKoZIhvcNAQELBQAwJDEiMCAGA1UEAwwZaHR0cHM6Ly9vdGV2cmVuYW1lc3RhLmN6LzAeFw0xOTEwMDEwOTM1MjhaFw0yMjA5MTUwOTM1MjhaMBQxEjAQBgNVBAMMCXN6cmMtdGVzdDCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAL7C+gGDVHuZSYz2MDq21UB/e3UXiU7L3iJvv8GEzJoi+SvauCU/Ui5oGc/w2MqZ21E463aDadjksRFSB+9z/uw2yNna+Wctg2RoY4CMZNdp/MxunRIT9/U0ecXVHPcqsnTnVykK1QYUv8BaGHLQH0Okk/7+SWHR/MMXcJ7OTI4owm8bFRKN/PFaUtCSNYxhUnP51quWwx6EXoHXZWAq//7YCZP+WL7dcmnql4JxjpZGq4lINqCGA8WXw0EuXbUs5vakl5SFmDMezmiO5IEi1Mk5vmv649p2gUa4qpVPgHkhCqOSRB0BAeEh63hZM05Z+HkDb6R8VYAdFW3ZEL0lXw8CAwEAAaOBsDCBrTAJBgNVHRMEAjAAMB0GA1UdDgQWBBRJIq+FqevCnV5u7guRRfs8k4KbmDBfBgNVHSMEWDBWgBTypM00nD30BAjXOIrO8l6xFnzxvaEopCYwJDEiMCAGA1UEAwwZaHR0cHM6Ly9vdGV2cmVuYW1lc3RhLmN6L4IUdCc7pFsR+OGBk+abd7ssRaIIM1EwEwYDVR0lBAwwCgYIKwYBBQUHAwIwCwYDVR0PBAQDAgeAMA0GCSqGSIb3DQEBCwUAA4IBAQBHSwJPSnC6G+978X/Lk4UMx1QMXmUpvaWnELBMyAcdpRsN9RsOsiJKLYiTAHFLHDwAF0cd/2ZxcwqHu2dX2jwVfOE+Z3UhHEBmvLTPBQq96y62KO4Px7//6gQchK+zER5ZfOP7jAqqziIu+SuI4xJ3zBgEGb4wr3EdQqonNnk6rZh7uJlnCWaoZACg5+S97aK77HaJgk775lFYhDiuQBRD6GKLJoqR1Yvg12RN0X1UbCV5hUF0UEOgHhbNNmZIU9qrKeVKefekDSjzd8xDIU6Ic5w3gKS01CecLQL7/tSpi/s3X+1f4yTjozurqNjUV7gBxcyYRw+4vE4aa4qx/gWd',
            'privateKey' => ''
        ]
    ];

    public function intro()
    {
    }

    public function idpInfo()
    {
        $this->set('title', 'Informace o NIA IdP - Identity Provider');
        try {

            $metadata = IdPMetadataParser::parseRemoteXML($this->metadata_url);
            $this->set(compact('metadata'));
        } catch (Exception $e) {
            $this->Flash->error($e->getMessage());
        }
    }

    public function sepInfo()
    {
        $this->set('title', 'Informace o testovacím SeP - Service Provider');
    }

    public function exampleStep1()
    {

    }

    public function PrivateAccess()
    {

    }

    public function ExternalLogin()
    {

    }

    public function ExternalLogout()
    {

    }

    public function SePConfiguration()
    {
        try {
            $idpMetadata = IdPMetadataParser::parseRemoteXML($this->metadata_url);
            IdPMetadataParser::injectIntoSettings($this->example_configuration, $idpMetadata);
        } catch (Exception $e) {
            $this->Flash->error($e->getMessage());
        }
        $this->example_configuration['sp']['privateKey'] = file_get_contents(CONFIG . 'private.key');

        $metadata = Metadata::builder($this->example_configuration['sp'], true, true);
        $metadata = Metadata::addX509KeyDescriptors($metadata, $this->example_configuration['sp']['x509cert']);
        $metadata = Metadata::signMetadata($metadata, $this->example_configuration['sp']['privateKey'], $this->example_configuration['sp']['x509cert']);
        return $this->response->withType('text/xml')->withStringBody($metadata);
    }
}
