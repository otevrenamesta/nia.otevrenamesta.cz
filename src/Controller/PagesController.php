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

use App\Saml\NiaContainer;
use App\Saml\NiaExtensions;
use App\Saml\NiaServiceProvider;
use Cake\Cache\Cache;
use Exception;
use OneLogin\Saml2\AuthnRequest;
use OneLogin\Saml2\IdPMetadataParser;
use OneLogin\Saml2\Metadata;
use OneLogin\Saml2\Settings;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Certificate\X509;
use SAML2\Compat\ContainerSingleton;
use SAML2\DOMDocumentFactory;
use SAML2\XML\md\EntityDescriptor;
use SAML2\XML\md\IDPSSODescriptor;

class PagesController extends AppController
{

    private $metadata_url = "https://tnia.eidentita.cz/FPSTS/FederationMetadata/2007-06/FederationMetadata.xml";
    private $example_configuration = [
        'strict' => false,
        'debug' => true,
        'baseurl' => 'https://nia.otevrenamesta.cz/',
        'security' => [
            'requestedAuthnContextComparison' => 'minimum',
            'requestedAuthnContext' => [
                'http://eidas.europa.eu/LoA/low'
            ]
        ],
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
        $this->set('title', 'Integrace - První krok');
    }

    public function exampleStep2()
    {
        $this->set('title', 'Integrace - Druhý krok');

        try {
            $idpMetadata = IdPMetadataParser::parseRemoteXML($this->metadata_url);
            $this->example_configuration = IdPMetadataParser::injectIntoSettings($this->example_configuration, $idpMetadata);
        } catch (Exception $e) {
            $this->Flash->error($e->getMessage());
        }
        $redirect_url = "https://tnia.eidentita.cz/FPSTS/saml2/basic";
        $authn = new AuthnRequest(new Settings($this->example_configuration));
        dump($authn->getXML());
    }

    public function test()
    {
        $start = time();

        $nia_container = new NiaContainer($this);
        $service_provider = new NiaServiceProvider();
        ContainerSingleton::setContainer($nia_container);

        $idp_metadata_url = 'https://tnia.eidentita.cz/FPSTS/FederationMetadata/2007-06/FederationMetadata.xml';
        $idp_metadata_contents = Cache::read('idp_metadata_contents');
        $from_cache = true;
        if ($idp_metadata_contents === false) {
            $idp_metadata_contents = file_get_contents($idp_metadata_url);
            Cache::write('idp_metadata_contents', $idp_metadata_contents);
            $from_cache = false;
        }
        $this->set('metadata_from_cache', $from_cache);

        $idp_metadata_domdocument = DOMDocumentFactory::fromString($idp_metadata_contents);
        $idp_metadata_root_domelement = $idp_metadata_domdocument->getElementsByTagName('EntityDescriptor')[0];
        $idp_descriptor = new EntityDescriptor($idp_metadata_root_domelement);

        $local_tnia_cert_data = file_get_contents(WWW_ROOT . 'tnia.crt');
        $local_tnia_cert = X509::createFromCertificateData($local_tnia_cert_data);

        $local_cert_data = file_get_contents(WWW_ROOT . 'szrc-test.crt');
        $local_cert = X509::createFromCertificateData($local_cert_data);

        $nia_public_key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $nia_public_key->loadKey($local_tnia_cert_data, false, true);
        $this->set('idp_descriptor_signature_valid', $idp_descriptor->validate($nia_public_key));

        $idp_sso_descriptor = false;
        foreach ($idp_descriptor->getRoleDescriptor() as $role_descriptor) {
            if ($role_descriptor instanceof IDPSSODescriptor) {
                $idp_sso_descriptor = $role_descriptor;
            }
        }
        $this->set(compact('idp_sso_descriptor'));

        $sso_redirect_login_url = false;
        $sso_post_login_url = false;

        if ($idp_sso_descriptor instanceof IDPSSODescriptor) {
            foreach ($idp_sso_descriptor->getSingleSignOnService() as $descriptorType) {
                if ($descriptorType->getBinding() === 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect') {
                    $sso_redirect_login_url = $descriptorType->getLocation();
                } else if ($descriptorType->getBinding() === 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST') {
                    $sso_post_login_url = $descriptorType->getLocation();
                }
            }
        }

        $this->set(compact('sso_redirect_login_url', 'sso_post_login_url'));

        $auth_request = new \SAML2\AuthnRequest();
        $auth_request->setId($nia_container->generateId());
        $auth_request->setIssuer($nia_container->getIssuer());
        $auth_request->setDestination($sso_redirect_login_url);
        $auth_request->setAssertionConsumerServiceURL(NiaServiceProvider::$AssertionConsumerServiceURL);
        $auth_request->setRequestedAuthnContext([
            'AuthnContextClassRef' => [NiaServiceProvider::LOA_LOW],
            'Comparison' => 'minimum'
        ]);

        $auth_request_xml_domelement = $auth_request->toUnsignedXML();
        $exts = new NiaExtensions($auth_request_xml_domelement);
        $exts->addAllDefaultAttributes();
        $auth_request_xml_domelement = $exts->toXML();
        $auth_request_xml = $auth_request_xml_domelement->ownerDocument->saveXML($auth_request_xml_domelement);

        $end = time();

        $this->set('took', $end - $start);
        $this->set(compact('idp_descriptor', 'local_tnia_cert', 'local_cert', 'auth_request', 'auth_request_xml'));
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
            $this->example_configuration = IdPMetadataParser::injectIntoSettings($this->example_configuration, $idpMetadata);
        } catch (Exception $e) {
            $this->Flash->error($e->getMessage());
        }
        $this->example_configuration['sp']['privateKey'] = file_get_contents(CONFIG . 'private.key');

        $metadata = Metadata::builder($this->example_configuration['sp'], true, true);
        $metadata = Metadata::addX509KeyDescriptors($metadata, $this->example_configuration['sp']['x509cert']);
        $metadata = Metadata::signMetadata($metadata, $this->example_configuration['sp']['privateKey'], $this->example_configuration['sp']['x509cert']);
        return $this->response->withType('text/xml')->withStringBody($metadata);
    }

    private function der2pem($der_data)
    {
        $pem = chunk_split(base64_encode($der_data), 64, "\n");
        $pem = "-----BEGIN CERTIFICATE-----\n" . $pem . "-----END CERTIFICATE-----\n";
        return $pem;
    }
}
