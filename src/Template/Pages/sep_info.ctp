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

<h2>Jak se stát Poskytovatelem Služeb (SeP)</h2>
<ul>
    <li>Pokud jste <strong>OVM (orgán státní moci nebo státem zřizovaná instituce)</strong> - stačí se přihlásit datovou schránkou vašeho statutárního orgánu (starostka, ředitel, ...) na stránkách <a href="https://www.identitaobcana.cz/Home">eIdentita.cz</a>, dole na stránce odkazem <a href="https://www.identitaobcana.cz/Sep/SepSignIn">Přihlásit se jako poskytovatel služby</a><br/>&nbsp;</li>
    <li>
        Pokud jste <strong>soukromoprávní subjekt (firma nebo osvč)</strong>, musíte nejdříve požádat o povolení, dle zákonem definovaného důvodu, který vás opravňuje vyžadovat ověřenou identitu, pak je proces stejný (přihlášení datovou schránkou statutárního orgánu, úroveň <a href="https://www.mojedatovaschranka.cz/static/ISDS/help/page8.html">"Oprávněná osoba"</a>)
        <br/>&nbsp;<ul>
            <li>Volnou formou můžete požádat na e-mailové adrese: eidentita@szrcr.cz</li>
            <li>E-mailová diskuze k oprávněnosti soukromoprávních subjektů (MVČR) zde <?= $this->Html->link('https://github.com/smarek/e-referendum.cz/issues/1')?></li>
        </ul>
    </li>
</ul>

<h2>Základy vytvoření SeP</h2>

<p>Pro začátek screenshot z konfiguračního rozhraní SeP na portálu <a href="https://twww.identitaobcana.cz/">https://twww.identitaobcana.cz/</a>
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
    <li>Formát metadat je SAML2.0 EntityDescriptor, v
        dokumentaci <?= $this->Html->link('eIDAS Message Format v1.2.pdf', 'https://ec.europa.eu/cefdigital/wiki/download/attachments/82773108/eIDAS%20SAML%20Message%20Format%20v.1.2%20Final.pdf?version=3&modificationDate=1571068651727&api=v2') ?>
        , příklad v sekci 6.1, popisující formát metadat na straně SeP
    </li>
</ul>
<h4>Adresa pro příjem vydaného tokenu (AuthResponse)</h4>
<ul>
    <li>URL na kterou bude přesměrován uživatel, která zajistí verifikaci a použití vráceného SAML AuthResponse</li>
    <li>Použitá metoda bude HTTP-REDIRECT nebo HTTP-POST (dle IdP metadat SingleSignOnService )</li>
    <li>V SAML2 AuthnRequest jde o parametr Audience</li>
</ul>
<h4>Adresa pro přesměrování uživatele po odhlášení (LogoutResponse)</h4>
<ul>
    <li>URL na kterou bude uživatel přesměrován po odhlášení</li>
    <li>Použitá metoda bude HTTP-REDIRECT</li>
</ul>
<h4>Unikátní URL adresa zabezpečené části Vašeho webu</h4>
<ul>
    <li>Nejedná se o nutně existující URL adresu, ale především o unikátní identifikátor SeP</li>
    <li>V SAML2 AuthnRequest jde o parametr Issuer</li>
</ul>
