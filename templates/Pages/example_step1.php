<?php
/**
 * @var $this AppView
 * @var $title string
 * @var $metadata EntityDescriptor
 * @var $valid boolean
 * @var $urls array
 */

use App\View\AppView;
use SAML2\XML\md\EntityDescriptor;

?>
<h1><?= $title ?></h1>

<p>Prvním krokem je získání informací z metadat IdP</p>
<p>V <?= $this->Html->link('dokumentaci', '') ?> je uveden odkaz na metadata NIA
    IdP <?= $this->Html->link('FederationMetadata.xml', 'https://tnia.identitaobcana.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml') ?></p>
<p>Více informací o NIA IdP metadatech je uvedeno na
    stránce <?= $this->Html->link('IdP - Úvod', ['controller' => 'Pages', 'action' => 'idpInfo']) ?></p>

<h2 id="steps">Postup</h2>
<ol>
    <li>Stažení souboru metadat</li>
    <li>Ověření obsahu metadat (dle RSA-SHA256 signatury XMLDSIG)</li>
    <li>Získání adresy pro přesměrování uživatele</li>
</ol>

<h3 id="metadata-download">1. stažení souboru a parsování jeho dat</h3>
<pre>
    <code class="php">
 &lt;?php
 // použijeme knihovnu simplesamlphp/saml2 z https://github.com/simplesamlphp/saml2
 use SAML2\XML\md\EntityDescriptor;
 use SAML2\DOMDocumentFactory;

 $metadata_url = "https://tnia.identitaobcana.cz/FPSTS/FederationMetadata/2007-06/FederationMetadata.xml";
 $metadata_string = file_get_contents($metadata_url);
 $metadata_dom = DOMDocumentFactory::fromString($metadata_string);
 $metadata = new EntityDescriptor($metadata_dom->documentElement);
 // také lze využít metodu DOMDocumentFactory::fromFile($filepath); pokud máte metadata stažena lokálně

</code></pre>

Objekt následovně obsahuje tato data:<br/>
<?php dump($metadata) ?>

<h3 id="metadata-verify">2. Ověření obsahu souboru</h3>
<pre>
    <code class="php">
 &lt;?php
 use RobRichards\XMLSecLibs\XMLSecurityKey;

 // soubor s certifikátem bychom měli mít uložen lokálně, aby validace podpisu proběhla korektně
 // na uvedené adrese je uložen NIA certifikát (PEM) z testovacího prostředí
 $tnia_cert_data = file_get_contents('https://nia.otevrenamesta.cz/tnia.crt');
 // z dat certifikátu vytvoříme klíč
 $tnia_key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
 $tnia_key->loadKey($tnia_cert_data, false, true);
 // a použijeme interní metodu EntityDescriptor->validate(XMLSecurityKey $key) pro validaci
 $valid = $metadata->validate($tnia_key);

    </code>
</pre>

Obsah proměnné valid: <?php dump($valid) ?>

<h3 id="extract-sso-login-url">3. Získání adresy pro přesměrování uživatele</h3>
<pre>
    <code class="php">
    &lt;?php
    use SAML2\Constants;
    use SAML2\XML\md\IDPSSODescriptor;
    use SAML2\XML\md\EntityDescriptor;

    private function extractSSOLoginUrls(EntityDescriptor $idp_descriptor){
        $idp_sso_descriptor = false;
        foreach ($idp_descriptor->getRoleDescriptor() as $role_descriptor) {
            if ($role_descriptor instanceof IDPSSODescriptor) {
                $idp_sso_descriptor = $role_descriptor;
            }
        }

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

    $urls = extractSSOLoginUrls($metadata);
    $redirect_url = $urls[Constants::BINDING_HTTP_REDIRECT];
    $post_url = $urls[Constants::BINDING_HTTP_POST];
    </code>
</pre>

Obsah proměnné urls: <?php dump($urls) ?>

<h2 id="link-step2"><?= $this->Html->link('Pokračovat na Druhý krok', ['controller' => 'Pages', 'action' => 'exampleStep2']) ?></h2>
