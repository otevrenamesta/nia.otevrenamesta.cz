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
use Cake\Utility\Text;
use DOMElement;
use Exception;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Assertion;
use SAML2\AuthnRequest;
use SAML2\Certificate\Key;
use SAML2\Compat\ContainerSingleton;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\EncryptedAssertion;
use SAML2\LogoutRequest;
use SAML2\Response;
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

    public function intro()
    {
    }

    public function idpInfo()
    {
        $this->set('title', 'Informace o NIA IdP - Identity Provider');
    }

    public function sepInfo()
    {
        $this->set('title', 'Informace o testovacím SeP - Service Provider');
    }

    public function sepMetadata()
    {
        $this->set('title', 'SeP - Metadata (EntityDescriptor / SPSSODescriptor)');
    }

    public function exampleStep2()
    {
        $this->exampleStep1();
        $this->set('title', 'Implementace(2) - Vytvoření požadavku (AuthnRequest)');
        $idp_descriptor = $this->getIdpDescriptor();
        $signed_request = $this->generateAuthnRequest($idp_descriptor);

        $xml = $signed_request->ownerDocument->saveXML();
        $query = gzdeflate($xml);
        $query = base64_encode($query);
        $query = urlencode($query);

        $urls = $this->extractSSOLoginUrls($idp_descriptor);
        $sso_redirect_login_url = $urls[Constants::BINDING_HTTP_REDIRECT];

        $link = $sso_redirect_login_url . (parse_url($sso_redirect_login_url, PHP_URL_QUERY) ? '&' : '?') . 'SAMLRequest=' . $query;

        $this->set(compact('signed_request', 'query', 'link'));
    }

    public function exampleStep1()
    {
        $this->set('title', 'Implementace(1) - Získání adresy pro přesměrování uživatele');

        $metadata = $this->getIdpDescriptor();
        $this->set(compact('metadata'));
        $this->set('urls', $this->extractSSOLoginUrls($metadata));

        $local_tnia_cert_data = file_get_contents(WWW_ROOT . 'tnia.crt');
        try {
            $tnia_key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
            $tnia_key->loadKey($local_tnia_cert_data, false, true);

            $valid = $metadata->validate($tnia_key);
            $this->set(compact('valid'));
        } catch (Exception $e) {
            $this->Flash->error($e->getMessage());
        }

    }

    private function getIdpDescriptor()
    {
        $metadata_string = $this->getIdpMetadataContents();
        $metadata_dom = DOMDocumentFactory::fromString($metadata_string);
        try {
            return new EntityDescriptor($metadata_dom->documentElement);
        } catch (Exception $e) {
            $this->Flash->error($e->getMessage());
        }
        return false;
    }

    private function getIdpMetadataContents()
    {
        $idp_metadata_url = 'https://tnia.identitaobcana.cz/FPSTS/FederationMetadata/2007-06/FederationMetadata.xml';
        $idp_metadata_contents = Cache::read('idp_metadata_contents');
        $from_cache = true;
        if ($idp_metadata_contents === false) {
            $idp_metadata_contents = file_get_contents($idp_metadata_url);
            Cache::write('idp_metadata_contents', $idp_metadata_contents);
            $from_cache = false;
        }
        $this->set('metadata_from_cache', $from_cache);
        return $idp_metadata_contents;
    }

    private function extractSSOLoginUrls(EntityDescriptor $idp_descriptor)
    {
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

        return [Constants::BINDING_HTTP_REDIRECT => $sso_redirect_login_url, Constants::BINDING_HTTP_POST => $sso_post_login_url];
    }

    private function extractSSOLogoutUrls(EntityDescriptor $idp_descriptor)
    {
        $idp_sso_descriptor = false;
        foreach ($idp_descriptor->getRoleDescriptor() as $role_descriptor) {
            if ($role_descriptor instanceof IDPSSODescriptor) {
                $idp_sso_descriptor = $role_descriptor;
            }
        }
        $this->set(compact('idp_sso_descriptor'));

        $sso_redirect_logout_url = false;
        $sso_post_logout_url = false;

        if ($idp_sso_descriptor instanceof IDPSSODescriptor) {
            foreach ($idp_sso_descriptor->getSingleLogoutService() as $descriptorType) {
                if ($descriptorType->getBinding() === Constants::BINDING_HTTP_REDIRECT) {
                    $sso_redirect_logout_url = $descriptorType->getLocation();
                } else if ($descriptorType->getBinding() === Constants::BINDING_HTTP_POST) {
                    $sso_post_logout_url = $descriptorType->getLocation();
                }
            }
        }

        return [Constants::BINDING_HTTP_REDIRECT => $sso_redirect_logout_url, Constants::BINDING_HTTP_POST => $sso_post_logout_url];
    }

    private function generateAuthnRequest(EntityDescriptor $idp_descriptor)
    {
        $nia_container = new NiaContainer($this);
        $service_provider = new NiaServiceProvider();
        ContainerSingleton::setContainer($nia_container);

        $urls = $this->extractSSOLoginUrls($idp_descriptor);
        $sso_redirect_login_url = $urls[Constants::BINDING_HTTP_REDIRECT];

        $auth_request = new AuthnRequest();
        $auth_request->setId($nia_container->generateId());
        $auth_request->setIssuer($nia_container->getIssuer());
        $auth_request->setDestination($sso_redirect_login_url);
        $auth_request->setAssertionConsumerServiceURL(NiaServiceProvider::$AssertionConsumerServiceURL);
        $auth_request->setRequestedAuthnContext([
            'AuthnContextClassRef' => [NiaServiceProvider::LOA_LOW],
            'Comparison' => 'minimum'
        ]);
        $auth_request->setAudiences([NiaServiceProvider::$AssertionConsumerServiceURL]);

        $auth_request_xml_domelement = $auth_request->toUnsignedXML();
        $exts = new NiaExtensions($auth_request_xml_domelement);
        $exts->addAllDefaultAttributes();
        $auth_request_xml_domelement = $exts->toXML();

        $auth_request_xml = $auth_request_xml_domelement->ownerDocument->saveXML($auth_request_xml_domelement);
        $auth_request_xml_domelement = DOMDocumentFactory::fromString($auth_request_xml);

        $auth_request_xml_domelement = $service_provider->insertSignature($auth_request_xml_domelement->documentElement);

        return $auth_request_xml_domelement;
    }

    public function test()
    {
        $start = microtime(true);

        $idp_descriptor = $this->getIdpDescriptor();
        $this->set(compact('idp_descriptor'));
        $this->set('urls', $this->extractSSOLoginUrls($idp_descriptor));

        $auth_request_xml = $this->generateAuthnRequest($idp_descriptor);
        $auth_request_xml = $auth_request_xml->ownerDocument->saveXML();

        $auth_request_encoded = gzdeflate($auth_request_xml);
        $auth_request_encoded = base64_encode($auth_request_encoded);
        $auth_request_encoded = urlencode($auth_request_encoded);


        $urls = $this->extractSSOLoginUrls($idp_descriptor);
        $sso_redirect_login_url = $urls[Constants::BINDING_HTTP_REDIRECT];

        $link = $sso_redirect_login_url . '?SAMLRequest=' . $auth_request_encoded;

        $end = microtime(true);

        $this->set('took', sprintf("%f %f %f", $start, $end, $end - $start));
        $this->set(compact('idp_descriptor', 'local_tnia_cert', 'local_cert', 'auth_request', 'auth_request_xml', 'auth_request_encoded', 'link'));
    }

    public function PrivateAccess()
    {

    }

    public function ExternalLogin()
    {
        $this->set('title', 'Implementace(3) - Zpracování výsledku autorizace');
        $nia_container = new NiaContainer($this);
        $service_provider = new NiaServiceProvider();
        ContainerSingleton::setContainer($nia_container);

        try {
            $saml_response_raw = $this->request->getData('SAMLResponse');
            $saml_response_raw = base64_decode($saml_response_raw, true /* striktní validace base64 */);
            $saml_response_dom = DOMDocumentFactory::fromString($saml_response_raw);

            $saml_response_dom->preserveWhiteSpace = false;
            $saml_response_dom->formatOutput = true;
            $saml_response_formatted = $saml_response_dom->saveXML();
            $this->set('dummy_response', false);
            $this->set('dummy_fail', false);
        } catch (Exception $e) {
            $this->set('saml_response_error', $e);
            $this->set('dummy_response', true);
            $dummy_fail = $this->request->getQuery('type') === 'failure';
            $this->set(compact('dummy_fail'));
            $doc = $dummy_fail ? 'dummy_idp_response.xml' : 'fail_idp_response.xml';
            $saml_response_raw = file_get_contents(WWW_ROOT . $doc);
            $saml_response_dom = DOMDocumentFactory::fromFile(WWW_ROOT . $doc);
            $saml_response_formatted = $saml_response_raw;
        }
        $this->set(compact('saml_response_raw', 'saml_response_dom', 'saml_response_formatted'));

        $local_tnia_cert_data = file_get_contents(WWW_ROOT . 'tnia.crt');
        $tnia_public_key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $tnia_public_key->loadKey($local_tnia_cert_data, false, true);

        $response = new Response($saml_response_dom->documentElement);
        $response->validate($tnia_public_key);
        $assertions = $response->getAssertions();
        $assertion = false;

        try {
            $local_private_key = new XMLSecurityKey(XMLSecurityKey::RSA_OAEP_MGF1P, ['type' => 'private']);
            $local_private_key->loadKey(file_get_contents(CONFIG . 'private.key'), false, false);
            foreach ($assertions as $a) {
                if ($a instanceof EncryptedAssertion) {
                    $assertion = $a->getAssertion($local_private_key);

                    $assertion_dom = $assertion->toXML();

                    $assertion_dom->ownerDocument->preserveWhiteSpace = false;
                    $assertion_dom->ownerDocument->formatOutput = true;

                    $assertion_xml = $assertion_dom->ownerDocument->saveXML();

                    $attributes = $assertion->getAttributes();
                    $current_address_key = "http://eidas.europa.eu/attributes/naturalperson/CurrentAddress";
                    $current_address_raw = isset($attributes[$current_address_key]) ? base64_decode(reset($attributes[$current_address_key])) : false;

                    $tradresaid_key = "http://schemas.identitaobcana.cz/moris/2016/identity/claims/tradresaid";
                    $tradresaid_raw = isset($attributes[$tradresaid_key]) ? base64_decode(reset($attributes[$tradresaid_key])) : false;


                    $idp_descriptor = $this->getIdpDescriptor();
                    $logout_url = $this->extractSSOLogoutUrls($idp_descriptor)[Constants::BINDING_HTTP_REDIRECT];
                    $logout_request = $this->generateLogoutRequest($idp_descriptor, $assertion);
                    $logout_request->ownerDocument->preserveWhiteSpace = false;
                    $logout_request->ownerDocument->formatOutput = true;
                    $logout_request_xml_string = $logout_request->ownerDocument->saveXML();

                    $logout_request_encoded = gzdeflate($logout_request_xml_string);
                    $logout_request_encoded = base64_encode($logout_request_encoded);
                    $logout_request_encoded = urlencode($logout_request_encoded);

                    $this->set(compact('current_address_raw', 'tradresaid_raw', 'logout_request_xml_string', 'logout_request_encoded', 'logout_url'));
                }
            }
        } catch (Exception $e) {
            $this->set('private_key_error', $e);
            $assertion = false;
            $assertion_xml = false;
        }

        $this->set(compact('response', 'assertion', 'assertion_xml', 'assertions'));
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        $this->Security->setConfig('unlockedActions', ['externalLogin','externalLogout']);
        return parent::beforeFilter($event);
    }

    public function ExternalLogout()
    {
        $this->set('title', 'Implementace(4) - Zpracování výsledku odhlášení');

        try {
            $saml_response_raw = $this->request->getData('SAMLResponse');
            $saml_response_raw = base64_decode($saml_response_raw, true /* striktní validace base64 */);
            $saml_response_dom = DOMDocumentFactory::fromString($saml_response_raw);

            $saml_response_dom->preserveWhiteSpace = false;
            $saml_response_dom->formatOutput = true;
            $saml_response_formatted = $saml_response_dom->saveXML();
            $this->set('dummy_response', false);
        } catch (Exception $e) {
            $this->set('saml_response_error', $e);
            $this->set('dummy_response', true);
            $doc ='dummy_idp_logout_response.xml';
            $saml_response_raw = file_get_contents(WWW_ROOT . $doc);
            $saml_response_dom = DOMDocumentFactory::fromFile(WWW_ROOT . $doc);
            $saml_response_formatted = $saml_response_raw;
        }
        $this->set(compact('saml_response_raw', 'saml_response_dom', 'saml_response_formatted'));
    }

    public function SePConfiguration()
    {
        $metadata_dom_signed = $this->generateSePMetadata();

        return $this->response->withType('text/xml')->withStringBody($metadata_dom_signed->ownerDocument->saveXML());
    }

    private function generateSePMetadata()
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
        return $metadata_dom_signed;
    }

    /**
     * @param EntityDescriptor $idp_descriptor
     * @param Assertion $assertion
     * @return DOMElement
     * @throws Exception
     */
    private function generateLogoutRequest(EntityDescriptor $idp_descriptor, Assertion $assertion)
    {
        $service_provider = new NiaServiceProvider();
        $nia_container = new NiaContainer($this);
        ContainerSingleton::setContainer($nia_container);

        $urls = $this->extractSSOLogoutUrls($idp_descriptor);
        $logout_redirect_url = $urls[Constants::BINDING_HTTP_REDIRECT];

        $logout_request = new LogoutRequest();
        $logout_request->setSessionIndex($assertion->getSessionIndex());
        $logout_request->setDestination($logout_redirect_url);
        $logout_request->setId(Text::uuid());
        $logout_request->setIssueInstant(time());
        $logout_request->setIssuer($nia_container->getIssuer());
        $logout_request->setNameId($assertion->getNameId());

        $logout_xml_dom = $logout_request->toUnsignedXML();
        $logout_xml_dom = $service_provider->insertSignature($logout_xml_dom, false);

        return $logout_xml_dom;
    }

}
