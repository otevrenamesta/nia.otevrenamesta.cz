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
        <p>Na tuto stránku jste se nedostali přesměrováním z NIA IdP, takže zobrazená data jsou pouze pro ukázku</p>
        <p>Pokud chcete vidět aktuální / živá data, pokračujte
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

<h2>Získání a ověření odpovědi</h2>

<pre>
    <code class="xml">
<?= str_replace('<', '&lt;', $saml_response_formatted) ?>
    </code>
</pre>

<?php
foreach ($this->getVars() as $varName) {
    dump($varName, $this->get($varName));
}

?>
