<?php

use App\View\AppView;

/**
 * @var $this AppView
 * @var $saml_response_raw string
 * @var $saml_response_formatted string
 * @var $saml_response_dom DOMDocument
 * @var $response \SAML2\Response
 * @var $dummy_response boolean
 * @var $assertion \SAML2\Assertion|bool
 * @var $assertions array
 * @var $assertion_xml string|bool
 * @var $title string
 */

?>
<h1><?= $title ?></h1>

<?php if ($dummy_response === true): ?>
    <aside>
        <p>Na tuto stránku jste se nedostali přesměrováním z NIA IdP, takže zobrazená data jsou statická a
            historická</p>
        <p>Pokud chcete vidět aktuální / živá data, postupujte podle návodu
            na <?= $this->Html->link('KROK 2 - Ukázková implementace', ['action' => 'exampleStep2']) ?></p>
    </aside>
<?php endif; ?>

<h2>Postup</h2>
<ul>
    <li>Získání a ověření odpovědi</li>
    <li>Rozšifrování EncryptedAssertion</li>
    <li>Získání informací o uživateli</li>
    <li>Přihlášení uživatele</li>
</ul>

<h2>Odpověď IdP (saml:Response)</h2>

<p>Takto vypadá XML podoba odpovědi NIA IdP</p>
<p>Odpověď by měla být podepsána známým certifikátem IdP z metadat nebo z jiného ověřeného zdroje</p>
<p>Odpověď taktéž obsahuje šifrovanou identitu uživatele v elementu saml:EncryptedAssertion</p>
<p>Hlavička saml:Response také obsahuje informace, které můžete použít k validaci odpovědi nebo implementaci přihlášení uživatele</p>

<aside>
    <p>Například atribut InResponseTo využívá identifikátor, který jsme v předchozím kroce zvolili, takže je možné původní požadavek najít a zjistit zda je stále platný případně zda není příliš starý</p>
</aside>

<pre>
    <code class="xml">
<?= str_replace('<', '&lt;', $saml_response_formatted) ?>
    </code>
</pre>

<h2>Získání a ověření odpovědi</h2>

<p>XML odpověď (saml:Response) je od NIA přítomna v HTTP POST datech (poslední krok u NIA je odeslání klasického
    formuláře)</p>
<p>Tato odpověď je v POST klíči <strong>SAMLResponse</strong> a je enkódována base64</p>
<p>Následující kód představuje (a v komentářích vysevětluje) jednotlivé kroky při získání a ověření odpovědi</p>

<pre>
    <code class="php">
        use SAML2\DOMDocumentFactory;
        use RobRichards\XMLSecLibs\XMLSecurityKey;

        // můžete použít soubor https://github.com/otevrenamesta/eidentita-example/blob/master/webroot/tnia.crt
        $tnia_public_key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $tnia_public_key->loadKey(file_get_contents('tnia.crt'), false, true);

        // pokud není přítomna odpověď
        if (!$_POST['SAMLResponse']) { exit("Chybí odpověď v POST datech"); }

        // získání z POST dat
        $post_raw = $_POST['SAMLResponse'];
        // dekódování
        $post_raw = base64_decode($post_raw, true /* striktní validace base64 */);

        // pokud data nejsou platně dekódována base64
        if ($post_raw === false) { exit("Data nejsou validní Base64"); }

        try {
          $post_dom = DOMDocumentFactory::fromString($post_raw);
        } catch (\Exception $e) {
          // UnparseableXmlException pokud data nejsou kompletní nebo nejsou validní XML
          // RuntimeException pokud je v datech neočekávaný obsah
          exit("Data nejsou platným XML");
        }

        $response = new Response($saml_response_dom->documentElement);
        try {
          if (!$response->validate($tnia_public_key)) {
            // false je pokud není žádný dostupný validátor
            exit("Není možné zkontrolovat podpis odpovědi");
          }
        } catch (\Exception $e) {
          // vyjímka bude první vyjímkou z potenciálně mnoha, která popisuje, proč podpis dokumentu není validní dle
          // daného veřejného klíče
          exit("Neplatný XML podpis");
        }
    </code>
</pre>

<h2>Dešifrování poskytnutých uživatelských identit (saml:Assertion)</h2>

<pre>
    <code class="php">
        // konstanta RSA_OAEP_MGF1P definuje algoritmus, který NIA využívá při XML přenosu šifrované odpovědi
        $local_private_key = new XMLSecurityKey(XMLSecurityKey::RSA_OAEP_MGF1P, ['type' => 'private']);
        // načtení privátního klíči k certifikátu, kterým byla podesaná žádost o autorizaci (saml:AuthnRequest)
        $local_private_key->loadKey(file_get_contents('private.key'), false, false);

        // získání přítomných autorizací
        $assertions = $response->getAssertions();
        $encrypted_assertion = false;
        try {
          foreach ($assertions as $a) {
            if ($a instanceof EncryptedAssertion) {
              // získání dešifrované Assertion z objektu EncryptedAssertion
              $encrypted_assertion = $a->getAssertion($local_private_key);
            }
          }
        } catch (\Exception $e) {
          exit("Nastala chyba při dešifrování XML")
        }

        // pokud nebyla nalezena žádná uživatelská identifikace
        if (!$encrypted_assertion) {
          exit("V datech chybí identifikace uživatele");
        }


    </code>
</pre>

<h2>Takto pak vypadá Assertion element v XML</h2>

<pre>
    <code class="xml">
<?= str_replace('<', '&lt;', $assertion_xml) ?>
    </code>
</pre>

<?php
foreach ($this->getVars() as $varName) {
    dump($varName, $this->get($varName));
}

?>
