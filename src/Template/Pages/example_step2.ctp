<?php
/**
 * @var $this AppView
 * @var $title string
 */

use App\View\AppView; ?>
<h1><?= $title ?></h1>

<p>Z kroku 1 jsme získali ověřenou URL adresu pro přesměrování uživatele na autorizaci</p>
<p>Nyní je třeba vytvořit autorizační požadavek a tento předat NIA IdP</p>

<h2>Postup</h2>
<ol>
    <li>Vytvoření XML požadavku na autorizaci</li>
    <li>Podepsání požadavku</li>
    <li>Získání finální podoby požadavku</li>
    <li>Přesměrování uživatele</li>
</ol>

<h2>Vytvoření a podepsání XML požadavku na autorizaci</h2>

<pre>
    <code class="php">
    private function generateAuthnRequest(EntityDescriptor $idp_descriptor)
    {
        // stejně jako v předchozích ukázkách, NiaContainer a NiaServiceProvider jsou
        // implementace specifické pro vaši aplikaci / produkční prostředí
        // jejich popis je mimo možnosti tohoto manuálu
        $nia_container = new NiaContainer($this);
        $service_provider = new NiaServiceProvider();
        ContainerSingleton::setContainer($nia_container);

        // získání url adresy, na kterou přesměrovat uživatele při metodě HTTP-REDIRECT
        $urls = $this->extractSSOLoginUrls($idp_descriptor);
        $sso_redirect_login_url = $urls[Constants::BINDING_HTTP_REDIRECT];

        // samotný AuthnRequest
        $auth_request = new AuthnRequest();
        // unikátní ID
        $auth_request->setId($nia_container->generateId());
        // Issuer, neboli "Unikátní URL adresa zabezpečené části Vašeho webu"
        $auth_request->setIssuer($nia_container->getIssuer());
        // explicitní deklarace příjemce zprávy
        $auth_request->setDestination($sso_redirect_login_url);
        // adresa kam se má uživatel přesměrovat při dokončení procesu na straně IdP
        $auth_request->setAssertionConsumerServiceURL(NiaServiceProvider::$AssertionConsumerServiceURL);
        // vyžadovaná úroveň ověření identity
        // LOW dovoluje využít NIA jméno+heslo+sms, stejně jako datovou schránku FO nebo identitu zahraničního občana
        // SUBSTANTIAL pak dovoluje méně variant
        // HIGH dovoluje pouze elektronický občanský průkaz
        $auth_request->setRequestedAuthnContext([
            'AuthnContextClassRef' => [NiaServiceProvider::LOA_LOW],
            'Comparison' => 'minimum'
        ]);

        // vygenerování nepodepsaného požadavku
        $auth_request_xml_domelement = $auth_request->toUnsignedXML();
        // přidání vyžadovaných atributů (informací o uživateli), element samlp:Extensions
        $exts = new NiaExtensions($auth_request_xml_domelement);
        $exts->addAllDefaultAttributes();
        $auth_request_xml_domelement = $exts->toXML();

        $auth_request_xml = $auth_request_xml_domelement->ownerDocument->saveXML($auth_request_xml_domelement);
        $auth_request_xml_domelement = DOMDocumentFactory::fromString($auth_request_xml);

        // vložení vlastního podpisu naším privátním klíčem
        $auth_request_xml_domelement = $service_provider->insertSignature($auth_request_xml_domelement->documentElement);

        return $auth_request_xml_domelement;
    }
    </code>
</pre>

<h2>AuthnRequest - obsah požadavku</h2>

<strong>Důležité náležitosti AuthnRequest</strong>
<ul>
    <li>samlp:Issuer - obsahuje identifikátor SeP dle konfigurace v administraci NIA</li>
    <li>samlp:AuthnRequest atribut AssertionConsumerServiceURL - obsahuje URL kam se přesměruje uživatel po dokončení
        procesu u IdP
    </li>
    <li>samlp:RequestedAuthnContext - obsahuje požadovanou úroveň jistoty identifikace uživatele</li>
    <li>eidas:RequestedAttributes - obsahuje seznam požadovaných informací o identitě, a zda jsou tyto vyžadovány
        (atribut isRequired) či nikoliv
    </li>
    <li>Požadavek musí být podepsán privátním klíčem, který odpovídá certifikátu v SeP metadatech (příp. v konfiguraci SeP v administraci NIA)</li>
</ul>

<pre>
    <code class="xml">
<?php
$signed_request->ownerDocument->preserveWhiteSpace = false;
$signed_request->ownerDocument->formatOutput = true;
echo str_replace('<', '&lt;', $signed_request->ownerDocument->saveXML())
?>
    </code>
</pre>

<h2>Získání finální podoby požadavku</h2>

<p>Adresa na kterou přesměrujeme uživatele se skládá následovně</p>
<ul>
    <li>IdP SSO HTTP-REDIRECT binding URL adresa (viz. IdP Metadata, element SingleSignOnService)</li>
    <li>GET parametr <strong>SAMLRequest</strong></li>
    <li>AuthnRequest požadavek, s validním obsahem, podepsaný, zkomprimovaný (gzdeflate), enkódovaný (base64) a
        url-enkódovaný (urlencode)
    </li>
</ul>

<pre>
<code class="php">
    // EntityDescriptor pro IdP
    $idp_descriptor = generateIdpDescriptor();
    // viz. výše
    $authn_request = generateAuthnRequest($idp_descriptor);
    // komprese a enkódování požadavku
    $xml = $signed_request->ownerDocument->saveXML();

    $query = gzdeflate($xml);
    $query = base64_encode($query);
    $query = urlencode($query);

    // získání URL adresy
    // $redirect_url je popsána v posledním bodě kroku 1
    $final_url = $redirect_url . '?SAMLRequest=' . $query;
</code>
</pre>

<h2>Obsah finální URL (vygenerováno právě teď):</h2>

<?php dump($link) ?>

<aside>
    <p>Tutoriál pracuje s metodou HTTP-REDIRECT, ale dle IdP metadat je možné proces provést i skrze HTTP-POST</p>
    <p>HTTP-POST spočívá ve vygenerování HTML Formuláře, který uživatel buďto odešle stiskem tlačítka, nebo bude odeslán
        automaticky, např. Javascriptem</p>
    <p>Pro HTTP-POST platí stejná pravidla zacházení s daty, jen se liší způsob předání AuthnRequestu, není potřeba
        provádět <strong>urlencode</strong>, jelikož data nejsou vkládána do URL adresy</p>
</aside>

<h2>Další krok</h2>
<p>Otevřením finální URL se spustí proces autorizace u NIA, jeho dokončením se vrátíte na tento tutoriál na Krok 3</p>

<h2><a href="<?= $link ?>">Otevřít finální URL</a></h2>