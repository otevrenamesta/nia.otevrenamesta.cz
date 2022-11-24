<?php

use App\View\AppView;

/**
 * @var $this AppView
 * @var $saml_response_raw string
 * @var $saml_response_formatted string
 * @var $saml_response_dom DOMDocument
 * @var $response \SAML2\Response
 * @var $dummy_response boolean
 * @var $title string
 */

?>
<h1><?= $title ?></h1>

<aside>
    <p>Tento krok je jen pro úplnost, není třeba zbytečně rozvádět, LogoutResponse lze bezpečně ignorovat</p>
</aside>

<h2>Obsah LogoutResponse</h2>

<p>LogoutResponse XML je opět poskytnuto v HTTP-POST parametru SAMLResponse, jako base64 enkódovaný XML text</p>

<pre><code class="xml"><?= str_replace('<', '&lt;', $saml_response_formatted) ?></code></pre>

<p>Jak je vidět, obsah odpovědi není tentokrát vůbec podepsán ani šifrován</p>
<p>StatusCode v tomto případě opět neznamená že odhlášení na straně IdP proběhlo, ale pouze že SAMLRequest odhlášení
    byl úspěšně zpracován a byla vygenerována tato odpověď (LogoutResponse)</p>

<?php if ($dummy_response === true): ?>
<aside>
    <p>Na tuto stránku jste se nedostali přesměrováním z NIA IdP, takže zobrazená data jsou statická a
        historická</p>
    <p>Pokud chcete vidět aktuální / živá data, postupujte podle návodu
        na <?= $this->Html->link('KROK 3 - Ukázková implementace', ['action' => 'ExternalLogin']) ?></p>
</aside>
<?php endif; ?>