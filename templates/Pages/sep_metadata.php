<?php
/**
 * @var $this AppView
 * @var $title string
 */

use App\View\AppView;

?>
<h1><?= $title ?></h1>

<aside>
    <p>Tato sekce nepopisuje nutnou část integrace s NIA, avšak metadata můžou být nezbytná, pokud ve své aplikaci
        chcete například povolit přihlášení pro občany jiných států.</p>
    <p>Zároveň jde o implementaci doporučení evropské komise k eIDAS, dle
        dokumentace <?= $this->Html->link('eIDAS Message Format v1.2.pdf', 'https://ec.europa.eu/cefdigital/wiki/download/attachments/82773108/eIDAS%20SAML%20Message%20Format%20v.1.2%20Final.pdf?version=3&modificationDate=1571068651727&api=v2') ?>
    </p>
    <p>Více je uvedeno na
        stránkách <?= $this->Html->link('https://ec.europa.eu/cefdigital/wiki/display/CEFDIGITAL/eIDAS+eID+Profile') ?>
    </p>
</aside>

<h2>Účel metadat</h2>
<ul>
    <li>Popisují celý SAML kompatibilní endpoint (SeP) vč. náležitostí bezpečnostních a aplikačních</li>
    <li>V případě NIA, lze použít k dynamickému poskytnutí šifrovacího/podepisovacího certifikátu vůči IdP</li>
    <li>Ostatní informace (vyjma certifikátu) jsou v rámci NIA staticky konfigurovány v administraci SeP, tedy
        jejich
        uvedení v
        konfiguraci není pro integraci nezbytné
    </li>
</ul>

<h2>Vytvoření metadat</h2>
<p>
    Následující kód je aktuálně využíván k generování metadat přímo zde na adrese: <?= $this->Html->link('https://nia.otevrenamesta.cz/SeP/Konfigurace.xml') ?>
</p>

<pre>
        <code class="php">
 &lt;?php

 // NiaServiceProvider vychází z SAML2\Configuration\ServiceProvider a implementace je mimo rozsah tohoto manuálu
 // na aktuální implementaci se můžete podívat zde: https://github.com/otevrenamesta/eidentita-example/blob/master/src/Saml/NiaServiceProvider.php
 $service_provider = new NiaServiceProvider();
 // to stejné platí o NiaContainer, zde: https://github.com/otevrenamesta/eidentita-example/blob/master/src/Saml/NiaServiceProvider.php
 $nia_container = new NiaContainer($this);
 ContainerSingleton::setContainer($nia_container);

 $descriptor = new EntityDescriptor();

 // kontaktní osoba není vyžadována
 $contact = new ContactPerson();
 $contact->setContactType('technical');
 $contact->setCompany('Otevřená Města z.s.');
 $contact->setGivenName('Marek');
 $contact->setSurName('Sebera');
 $contact->setEmailAddress(['marek.sebera@gmail.com']);

 // organizace taktéž není vyžadována
 $org = new Organization();
 $org->setOrganizationDisplayName(['cz' => 'Otevřená Města z.s.']);
 $org->setOrganizationName(['cz' => 'Otevřená Města z.s.']);
 $org->setOrganizationURL(['cz' => 'https://github.com/otevrenamesta/eidentita-example']);

 $local_cert_x509_cert = new X509Certificate();
 // metoda $service_provider->getCertificateData() vrací čistě base64 obsah certifikátu, bez --- BEGIN CERTIFICATE --- a --- END CERTIFICATE --- uvození
 $local_cert_x509_cert->setCertificate($service_provider->getCertificateData());
 $local_cert_x509_data = new X509Data();
 $local_cert_x509_data->setData([$local_cert_x509_cert]);

 $key_info = new KeyInfo();
 $key_info->addInfo($local_cert_x509_data);

 // v doporučení dokumentace je uvádět oba klíče, vzhledem k tomu že se mohou lišit a KeyDescriptor může v jednu chvíli popsat jen jeden use-case
 $sign_key_descriptor = new KeyDescriptor();
 $sign_key_descriptor->setUse(Key::USAGE_SIGNING);
 $sign_key_descriptor->setKeyInfo($key_info);

 $enc_key_descriptor = new KeyDescriptor();
 $enc_key_descriptor->setUse(Key::USAGE_ENCRYPTION);
 $enc_key_descriptor->setKeyInfo($key_info);

 // vytváření některých elementů je zbytečně komplikované, saml2 knihovna nemá lepší nástroje
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
 // formát identifikátoru identity ve vrácených datech (Assertion)
 $spsso->setNameIDFormat([
     Constants::NAMEFORMAT_BASIC,
     Constants::NAMEFORMAT_UNSPECIFIED,
     Constants::NAMEFORMAT_URI
 ]);

 $descriptor->addRoleDescriptor($spsso);

 // identifikátor EntityDescriptoru musí být jednoznačný, aby se předešlo střetu registrací ve federativním modelu IdP
 $descriptor->setID($nia_container->generateId());
 $descriptor->setEntityID($service_provider->getEntityId());
 // osobně nastavuji expiraci na 1 týden, s obnovením vždy o půlnoci z neděle na pondělí
 $descriptor->setValidUntil(strtotime('next monday', strtotime('tomorrow')));

 $metadata_dom = $descriptor->toXML();

 // md:Extensions podle saml2.0 metadata se musí bohužel vytvořit mimo saml2 knihovnu, která jej ne zcela podporuje
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

 // podpis dat probíhá stejně jako u podpisu jakýchkoli jiných XML dat metodami dle XMLDSig specifikací, privátním klíčem
 // implementaci podpisu si můžete prohlédnout v NiaServiceProvider implementaci https://github.com/otevrenamesta/eidentita-example/blob/master/src/Saml/NiaServiceProvider.php#L34
 $metadata_dom_signed = $service_provider->insertSignature($metadata_dom);

 // výsledné XML je následně z objektu dostupné pod voláním
 // $metadata_dom_signed->ownerDocument->saveXML();
</code>
    </pre>

<h2>Validace vytvořených metadat</h2>

<p> Pro validaci metadat je možné použít XSD schémata SAML2.0, pro potřeby vývoje jsem vytvořil malý validační a
    instalační skript, který využívá nástroje xmllint z balíčku libxml2-utils (debian)
</p>
<p> dokumentaci a soubory validátoru můžete získat
    zde: <?= $this->Html->link('https://github.com/otevrenamesta/eidentita-example/tree/master/webroot/validate') ?>
</p>

<p>Existují také online nástroje, jako např. <?= $this->Html->link('https://www.samltool.com/validate_xml.php') ?></p>
