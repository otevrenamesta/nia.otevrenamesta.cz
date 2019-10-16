<?php
/**
 * @var $this AppView
 * @var $title string
 */

use App\View\AppView; ?>
<h1><?= $title ?></h1>

<p>Prvním krokem je získání informací z metadat IdP</p>
<p>V <?= $this->Html->link('dokumentaci', '') ?> je uveden odkaz na metadata NIA
    IdP <?= $this->Html->link('FederationMetadata.xml', 'https://tnia.eidentita.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml') ?></p>
<p>Více informací o NIA IdP metadatech je uvedeno na
    stránce <?= $this->Html->link('IdP - Úvod', ['controller' => 'Pages', 'action' => 'idpInfo']) ?></p>

<h2>Postup</h2>
<ol>
    <li>Stažení souboru metadat</li>
    <li>Ověření obsahu metadat (dle RSA-SHA256 signatury XMLDSIG)</li>
    <li>Získání adresy pro přesměrování uživatele</li>
</ol>

<h3>1. stažení souboru</h3>
<pre>
    <code class="php">
 &lt;?php
 // použijeme knihovnu OneLogin/php-saml z https://github.com/onelogin/php-saml
 use OneLogin\Saml2\IdPMetadataParser;
 $metadata_url = 'https://tnia.eidentita.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml';
 $metadata_string = file_get_contents($metadata_url);
 $metadata = IdPMetadataParser::parseXML($metadata_string);
 // také lze využít metodu IdPMetadataParser::parseRemoteXML(string $url);

</code></pre>

<h3>2. Ověření obsahu souboru</h3>
<pre>
    <code class="php">
 &lt;?php
 use OneLogin\Saml2\Utils;
 use OneLogin\Saml2\IdPMetadataParser;

 /*
  * Převede binární DER formát na PEM formát (base64 enkódovanou formu)
  */
 function der2pem($der_data) {
   $pem = chunk_split(base64_encode($der_data), 64, "\n");
   $pem = "-----BEGIN CERTIFICATE-----\n".$pem."-----END CERTIFICATE-----\n";
   return $pem;
 }

 // Pro ukázku používáme veřejný klíč přímo z metadat, správně by soubor měl být uložen na serveru SeP aby se zabránilo kompromitaci
 $cert = der2pem(base64_decode($metadata['idp']['x509cert']));
 $metadata_xml_dom = Utils::loadXML(new DOMDocument(), $metadata_string);
 $signatureOK = Utils::validateSign($metadata_xml_dom, $cert);
 // $signatureOK bude true pokud jsou uvedené XMLDSIG podpisy platné a odpovídají poskytnutému certifikátu

    </code>
</pre>

<h3>3. Získání adresy pro přesměrování uživatele</h3>
<pre>
    <code class="php">
&lt;?php
$http_redirect_url = $metadata['idp']['singleSignOnService']['url'];
    </code>
</pre>

<h2><?= $this->Html->link('Pokračovat na Druhý krok', ['controller' => 'Pages', 'action' => 'exampleStep2']) ?></h2>