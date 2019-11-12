<h1>Vítejte v ukázkové integraci s NIA (<?= $this->Html->link('eidentita.cz', 'https://www.eidentita.cz/Home') ?>)</h1>

<p>Cílem tohoto projektu je představit a zjednodušit integraci s NIA (Národním bodem pro
    identifikaci a autentizaci)
</p>

<p>NIA je státem (MVČR, SZČR) poskytovaná služba pro přihlášení uživatelů ke službám veřejné
    správy a třetích stran pomocí Datové schránky, občanského průkazu s čipem, nebo uživatelským účtem národní identitní
    autority
</p>

<h2>Tento projekt má 3 části</h2>

<h3>
    1. <?= $this->Html->link('NIA IdP - Správa Základních Registrů ČR - Dokumentace', ['controller' => 'Pages', 'action' => 'idpInfo']) ?></h3>
<p>
    IdP (Identity Provider), neboli poskytovatel identit (zprostředkovatel přihlášení)<br/>
    Pokud vás zajímá jaké služby poskytuje IdP, jak se o těchto službách dozvíte, pokračujte zde:
    <?= $this->Html->link('IdP - Úvod', ['controller' => 'Pages', 'action' => 'idpInfo']) ?>
</p>

<h3>
    2. <?= $this->Html->link('SeP - Poskytovatel služeb - Dokumentace', ['controller' => 'Pages', 'action' => 'sepInfo']) ?></h3>
<p>
    SeP (Service Provider), neboli poskytovatel služeb, může být orgán veřejné správy nebo soukromý subjekt<br/>
    Pokud vás zajímá jak se můžete stát SeP a jak se integrovat s NIA IdP, přečtěte si více zde:
    <?= $this->Html->link('SeP - Úvod', ['controller' => 'Pages', 'action' => 'sepInfo']) ?>
</p>
<p>Případně informace o SeP ve formátu SAML EntityDescriptor/SPSSOEntityDescriptor vč. kódu k publikaci takových
    metadat, <br/>můžete vidět zde:
    <?= $this->Html->link('SeP - Metadata', ['controller' => 'Pages', 'action' => 'sepMetadata']) ?></p>

<h3>
    3. <?= $this->Html->link('Ukázkový komentovaný průchod integrací', ['controller' => 'Pages', 'action' => 'exampleStep1']) ?></h3>
<p>
    Chcete vidět jak integrace a komunikace s NIA IdP vypadá v praxi z pohledu uživatele i programátora?<br/>
    Začněte zde: <?= $this->Html->link('První krok', ['controller' => 'Pages', 'action' => 'exampleStep1']) ?>
</p>
<hr/>
<h2>Tento projekt vytvořila:</h2>

<img src="https://gitlab.com/otevrenamesta/documents/raw/master/grafika/logo_300.png" alt="Otevřená Města Logo"
     class="pure-img-responsive">