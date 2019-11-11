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
    <li>Zašifrování požadavku</li>
    <li>Přesměrování uživatele</li>
</ol>

<h3>Vytvoření XML požadavku na autorizaci</h3>
