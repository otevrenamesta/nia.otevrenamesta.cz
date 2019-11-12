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
use Cake\Event\Event;
use Cake\Routing\Router;
use DOMDocument;
use Exception;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\AuthnRequest;
use SAML2\Certificate\Key;
use SAML2\Certificate\X509;
use SAML2\Compat\ContainerSingleton;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\EncryptedAssertion;
use SAML2\Response;
use SAML2\Utils;
use SAML2\XML\Chunk;
use SAML2\XML\ds\KeyInfo;
use SAML2\XML\ds\X509Certificate;
use SAML2\XML\ds\X509Data;
use SAML2\XML\md\ContactPerson;
use SAML2\XML\md\EntityDescriptor;
use SAML2\XML\md\IDPSSODescriptor;
use SAML2\XML\md\IndexedEndpointType;
use SAML2\XML\md\KeyDescriptor;
use SAML2\XML\md\Organization;
use SAML2\XML\md\SPSSODescriptor;

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

        $local_public_key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $local_public_key->loadKey(file_get_contents(CONFIG . 'private.key'), false, false);

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
                if ($descriptorType->getBinding() === Constants::BINDING_HTTP_REDIRECT) {
                    $sso_redirect_login_url = $descriptorType->getLocation();
                } else if ($descriptorType->getBinding() === Constants::BINDING_HTTP_POST) {
                    $sso_post_login_url = $descriptorType->getLocation();
                }
            }
        }

        $this->set(compact('sso_redirect_login_url', 'sso_post_login_url'));

        $auth_request = new AuthnRequest();
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
        $auth_request_xml_domelement = DOMDocumentFactory::fromString($auth_request_xml);

        Utils::insertSignature($local_public_key, [$local_cert_data], $auth_request_xml_domelement->documentElement, $auth_request_xml_domelement->getElementsByTagName('Issuer')->item(0)->nextSibling);

        /** @var DOMDocument $auth_request_xml_domelement */
        $auth_request_xml = $auth_request_xml_domelement->saveXML();

        $auth_request_encoded = gzdeflate($auth_request_xml);
        $auth_request_encoded = base64_encode($auth_request_encoded);
        $auth_request_encoded = urlencode($auth_request_encoded);

        $link = $sso_redirect_login_url . '?SAMLRequest=' . $auth_request_encoded;

        $end = time();

        $this->set('took', $end - $start);
        $this->set(compact('idp_descriptor', 'local_tnia_cert', 'local_cert', 'auth_request', 'auth_request_xml', 'auth_request_encoded', 'link'));
    }

    public function PrivateAccess()
    {

    }

    public function ExternalLogin()
    {
        $nia_container = new NiaContainer($this);
        $service_provider = new NiaServiceProvider();
        ContainerSingleton::setContainer($nia_container);

        $saml_response_raw = $this->request->getData('SAMLResponse');
        $saml_response_raw = base64_decode($saml_response_raw);

        $saml_response_dom = DOMDocumentFactory::fromString($saml_response_raw);

        $local_private_key = new XMLSecurityKey(XMLSecurityKey::RSA_OAEP_MGF1P, ['type' => 'private']);
        $local_private_key->loadKey(file_get_contents(CONFIG . 'private.key'), false, false);

        $response = new Response($saml_response_dom->documentElement);
        $assertion = $response->getAssertions()[0];
        if ($assertion instanceof EncryptedAssertion) {
            $assertion = $assertion->getAssertion($local_private_key);
        }

        $this->set(compact('saml_response_raw', 'saml_response_dom', 'response', 'assertion'));
    }

    public function beforeFilter(Event $event)
    {
        $this->Security->setConfig('unlockedActions', ['externalLogin']);
        return parent::beforeFilter($event);
    }

    public function ExternalLogout()
    {

    }

    public function SePConfiguration()
    {
        $service_provider = new NiaServiceProvider();
        $nia_container = new NiaContainer($this);
        ContainerSingleton::setContainer($nia_container);

        $descriptor = new EntityDescriptor();

        $contact = new ContactPerson();
        $contact->setContactType('technical');
        $contact->setCompany('Otevřená Města z.s.');
        $contact->setGivenName('Marek');
        $contact->setSurName('Sebera');
        $contact->setEmailAddress(['marek.sebera@gmail.com']);

        $org = new Organization();
        $org->setOrganizationDisplayName(['cz' => 'Otevřená Města z.s.']);
        $org->setOrganizationName(['cz' => 'Otevřená Města z.s.']);
        $org->setOrganizationURL(['cz' => 'https://github.com/otevrenamesta/eidentita-example']);

        $local_cert_x509_cert = new X509Certificate();
        $local_cert_x509_cert->setCertificate($service_provider->getCertificateData());
        $local_cert_x509_data = new X509Data();
        $local_cert_x509_data->setData([$local_cert_x509_cert]);

        $key_info = new KeyInfo();
        $key_info->addInfo($local_cert_x509_data);

        $sign_key_descriptor = new KeyDescriptor();
        $sign_key_descriptor->setUse(Key::USAGE_SIGNING);
        $sign_key_descriptor->setKeyInfo($key_info);

        $enc_key_descriptor = new KeyDescriptor();
        $enc_key_descriptor->setUse(Key::USAGE_ENCRYPTION);
        $enc_key_descriptor->setKeyInfo($key_info);

        $doc = DOMDocumentFactory::create();
        $enc_method_dom = $doc->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'EncryptionMethod');
        $enc_method_dom->setAttribute('Algorithm', XMLSecurityKey::AES256_CBC);
        $enc_method = new Chunk($enc_method_dom);

        $enc_key_descriptor->setEncryptionMethod([$enc_method]);

        $acs = new IndexedEndpointType();
        $acs->setIsDefault(true);
        $acs->setBinding(Constants::BINDING_HTTP_POST);
        $acs->setIndex(1);
        $acs->setLocation(Router::url(['action' => 'ExternalLogin', 'controller' => 'Pages'], true));

        $spsso = new SPSSODescriptor();
        $spsso->setAuthnRequestsSigned(true);
        $spsso->setWantAssertionsSigned(true);
        $spsso->addProtocolSupportEnumeration('urn:oasis:names:tc:SAML:2.0:protocol');
        $spsso->addKeyDescriptor($sign_key_descriptor);
        $spsso->addKeyDescriptor($enc_key_descriptor);
        $spsso->setOrganization($org);
        $spsso->addContactPerson($contact);
        $spsso->addAssertionConsumerService($acs);
        $spsso->setNameIDFormat([
            Constants::NAMEFORMAT_BASIC,
            Constants::NAMEFORMAT_UNSPECIFIED,
            Constants::NAMEFORMAT_URI
        ]);

        $descriptor->addRoleDescriptor($spsso);

        $descriptor->setID($nia_container->generateId());
        $descriptor->setEntityID($service_provider->getEntityId());
        $descriptor->setValidUntil(strtotime('next monday', strtotime('tomorrow')));

        $metadata_dom = $descriptor->toXML();

        $extensions = $metadata_dom->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'md:Extensions');
        $sptype = $metadata_dom->ownerDocument->createElementNS('http://eidas.europa.eu/saml-extensions', 'eidas:SPType');
        $sptype->nodeValue = 'public';
        $extensions->appendChild($sptype);
        $digest_method = $metadata_dom->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:algsupport', 'alg:DigestMethod');
        $digest_method->setAttribute('Algorithm', XMLSecurityDSig::SHA256);
        $extensions->appendChild($digest_method);
        $signing_method = $metadata_dom->ownerDocument->createElementNS('urn:oasis:names:tc:SAML:metadata:algsupport', 'alg:SigningMethod');
        $signing_method->setAttribute('MinKeySize', 256);
        $signing_method->setAttribute('Algorithm', XMLSecurityKey::RSA_SHA256);
        $extensions->appendChild($signing_method);

        $metadata_dom->appendChild($extensions);

        $metadata_dom_signed = $service_provider->insertSignature($metadata_dom);
        
        return $this->response->withType('text/xml')->withStringBody($metadata_dom_signed->ownerDocument->saveXML());
    }

}
