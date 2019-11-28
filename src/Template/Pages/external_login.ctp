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
 * @var $dummy_fail boolean
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
    <div class="pure-g">
        <div class="pure-u-1-2 b-1 text-center">
            <h3 class="<?= $dummy_fail ? 'tick-red' : 'tick-green' ?>"><?= $this->Html->link('Zobrazit data úspěšné autorizace', ['type' => 'success']) ?></h3>
        </div>
        <div class="pure-u-1-2 b-1 text-center">
            <h3 class="<?= $dummy_fail ? 'tick-green' : 'tick-red' ?>"><?= $this->Html->link('Zobrazit data neúspěšné autorizace', ['type' => 'failure']) ?></h3>
        </div>
    </div>
<?php endif; ?>

    <h2 id="steps">Postup</h2>
    <ul>
        <li>Získání a ověření odpovědi</li>
        <li>Rozšifrování EncryptedAssertion</li>
        <li>Získání informací o uživateli</li>
        <li>Přihlášení uživatele</li>
    </ul>

    <h2 id="response-raw">Odpověď IdP (saml:Response)</h2>

    <p>Takto vypadá XML podoba odpovědi NIA IdP</p>
    <p>Odpověď by měla být podepsána známým certifikátem IdP z metadat nebo z jiného ověřeného zdroje</p>
    <p>Odpověď taktéž obsahuje šifrovanou identitu uživatele v elementu saml:EncryptedAssertion</p>
    <p>Hlavička saml:Response také obsahuje informace, které můžete použít k validaci odpovědi nebo implementaci
        přihlášení uživatele</p>

    <aside>
        <p>Například atribut InResponseTo využívá identifikátor, který jsme v předchozím kroce zvolili, takže je možné
            původní požadavek najít a zjistit zda je stále platný případně zda není příliš starý</p>
    </aside>

<?php if ($dummy_fail): ?>
    <aside class="aside-red">
        <p>Přestože jsou aktuálně zobrazena data neúspěšné autorizace, tak element samlp:StatusCode obsahuje hodnotu
            "urn:oasis:names:tc:SAML:2.0:status:Success"</p>
        <p><a href="#assertion-formatted">Vysvětlení je níže v zobrazení dešifrovaného Assertion XML</a></p>
    </aside>
<?php endif; ?>

    <pre>
    <code class="xml">
<?= str_replace('<', '&lt;', $saml_response_formatted) ?>
    </code>
</pre>

    <h2 id="response-basics">Získání a ověření odpovědi</h2>

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

    <h2 id="assertion-decrypt">Dešifrování poskytnutých uživatelských identit (saml:Assertion)</h2>

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

    <h2 id="assertion-formatted">Takto pak vypadá Assertion element v XML</h2>

    <aside>
        <p>Assertion obsahuje popis poskytnuté odpovědi, zda je platná (příp. zda autorizace selhala), jakou mají data
            platnost, ID původního požadavku z dokumentu AuthRequest</p>
        <p>A především v sekci saml:AttributeStatement jsou obsaženy jednotlivé požadované atributy a jejich obsah,
            pokud je k dispozici a pokud jej uživatel povolil poskytnout</p>
    </aside>

<?php if ($dummy_fail): ?>
    <aside class="aside-red">
        <p>Přestože je v samotném samlp:Response element samlp:StatusCode s hodnotou
            "urn:oasis:names:tc:SAML:2.0:status:Success", tak StatusCode jen indikuje zda proces proběhl korektně,
            nikoli zda autorizace proběhla korektně</p>
        <p>Informaci o selhané autorizaci pak vidíme v Assertion elementu
            saml:Attribute[Name="urn:oasis:names:tc:SAML:2.0:protocol/statuscode"] kde hodnotou / obsahem příslušného
            saml:AttributeValue je status AuthnFailed</p>
    </aside>
<?php endif; ?>

    <pre>
    <code class="xml">
<?= str_replace('<', '&lt;', $assertion_xml) ?>
    </code>
</pre>

<?php if (!$dummy_fail): ?>
    <h2 id="non-trivial-attributes">Obsah netriviálních atributů</h2>
    <p>Výše je vidět, že pro většinu požadovaných údajů nám byla poskytnuta odpověď, některé elementy však neobsahují
        triviální obsah (text, číslo, identifikátor)</p>
    <p>Obsah těchto elementů je Base64 enkódovaná podoba XML obsahu, který by měl být validní podle XSD schématů
        uvedených v příručce SeP sekce 8.4</p>

    <h3>např. CurrentAddress (typ CurrentAddressType)</h3>
    <pre><code class="xml"><?= str_replace('<', '&lt;', $current_address_raw) ?></code></pre>
<?php endif; ?>