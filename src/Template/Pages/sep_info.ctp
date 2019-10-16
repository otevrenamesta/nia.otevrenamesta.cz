<?php
/**
 * @var $this AppView
 * @var $title string
 */

use App\View\AppView; ?>
<h1><?= $title ?></h1>

<p>
    SeP (Service Provider) - tedy aplikace která poskytuje služby přihlášeným uživatelům a má právo (právní nárok)
    identifikovat uživatele pomocí
    služeb <?= $this->Html->link('NIA IdP', ['controller' => 'Pages', 'action' => 'idpInfo']) ?>
</p>
<p>
    SeP typicky konfigurační informace poskytovateli (IdP) předává v požadavku, kvůli bezpečnosti se však v případě NIA
    tyto údaje konfigurují staticky na webovém rozhraní eIdentita.cz
</p>

<aside>
    <p>
        Testovací a produkční prostředí NIA IdP jsou striktně odděleny, uživatelské účty, konfigurace nebo probíhající
        komunikace nejsou propojeny nebo synchronizovány.
    </p>
    <p>
        Pro přihlášení do testovacího prostředí NIA IdP je třeba mít zřízenou testovací Datovou Schránku
        (<?= $this->Html->link('https://www.czebox.cz/') ?>)
    </p>
    <p>
        Do produkčního prostředí NIA IdP pak lze přistoupit klasickou produkční datovou schránkou OVM (orgánu veřejné
        moci) nebo povolenou schránkou soukromoprávního subjektu.
    </p>
</aside>

<h2>Základy vytvoření SeP</h2>

<p>Pro začátek screenshot z konfiguračního rozhraní SeP na portálu <a href="https://twww.eidentita.cz/">https://twww.eidentita.cz/</a>
    (testovací prostředí)</p>
<img src="/img/sep_konfigurace.png" alt="SeP ukázková konfigurace">

<h3>Popis jednotlivých parametrů</h3>
<h4>Adresa pro načtení veřejné části certifikátu z metadat (URL)</h4>
<ul>
    <li>SeP může ale nemusí vystavit svou vlastní
        konfiguraci (například <?= $this->Html->link('https://nia.otevrenamesta.cz/SeP/Konfigurace.xml') ?> )
    </li>
    <li>Součástí konfigurace je i certifikát, kterým se na straně NIA IdP šifrují odpovědi</li>
    <li>Pokud je tato konfigurace vč. certifikátu, lze jej použít pro dynamickou aktualizaci certifikátu, místo statické
        konfigurace na portále eIdentita
    </li>
</ul>
<h4>Adresa pro příjem vydaného tokenu (AuthResponse)</h4>
<ul>
    <li>URL na kterou bude přesměrován uživatel, která zajistí verifikaci a použití vráceného SAML AuthResponse</li>
    <li>Použitá metoda bude HTTP-REDIRECT nebo HTTP-POST (dle IdP metadat SingleSignOnService )</li>
</ul>
<h4>Adresa pro přesměrování uživatele po odhlášení (LogoutResponse)</h4>
<ul>
    <li>URL na kterou bude uživatel přesměrován po odhlášení</li>
    <li>Použitá metoda bude HTTP-REDIRECT (dle IdP metadat SingleLogoutService)</li>
</ul>
<h4>Unikátní URL adresa zabezpečené části Vašeho webu</h4>
<ul>
    <li>Nejedná se o nutně existující URL adresu, ale především o unikátní identifikátor SeP</li>
    <li>Tento identifikátor se používá v AuthnResponse jako parametr AudienceRestriction/Audience</li>
</ul>