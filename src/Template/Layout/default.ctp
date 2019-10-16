<?php
/**
 * @var $this AppView
 */

use App\View\AppView; ?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= isset($title) ? $title : '' ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css"
          integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/grids-responsive-min.css"
          crossorigin="anonymous">

    <link rel="stylesheet"
          href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.10/styles/default.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.10/highlight.min.js"></script>

    <?= $this->Html->css(['style']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>

<div id="layout" class="pure-g">
    <div class="sidebar pure-u-1 pure-u-md-1-4">
        <div class="header">
            <h1 class="brand-title"><a href="/" class="">NIA - eIdentita</a></h1>
            <h2 class="brand-tagline"><a href="/">Kvalifikovaný poskytovatel Otevřená&nbsp;Města</a></h2>

            <nav class="nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a class="pure-button" href="https://github.com/otevrenamesta/eidentita-example/wiki">Dokumentace</a>
                    </li>
                    <li class="nav-item">
                        <a class="pure-button" href="https://github.com/otevrenamesta/eidentita-example">Zdrojový
                            kód</a>
                    </li>
                </ul>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a class="pure-button"
                           href="<?= $this->Url->build(['controller' => 'Pages', 'action' => 'sepInfo']) ?>">SeP - Úvod</a>
                    </li>
                    <li class="nav-item">
                        <a class="pure-button"
                           href="<?= $this->Url->build(['controller' => 'Pages', 'action' => 'idpInfo']) ?>">IdP -
                            Úvod</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="content pure-u-1 pure-u-md-3-4 bottom-spacing">
        <?= $this->fetch('content') ?>
    </div>
</div>

</body>
</html>
