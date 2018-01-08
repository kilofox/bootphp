<?php
use Bootphp\HTML;
use Bootphp\Route;
use Bootphp\Core;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?= $title ?> | Bootphp Docs</title>
        <?php
        foreach ($styles as $style => $media)
            echo HTML::style($style, array('media' => $media), null, true), "\n";
        ?>
        <?php
        foreach ($scripts as $script)
            echo HTML::script($script, null, null, true), "\n";
        ?>
    </head>
    <body>
        <div id="kodoc-header">
            <div class="container">
                <a href="http://kilofox.net/" id="kodoc-logo">
                    <img src="<?= Route::url('doc/media', array('file' => 'img/bootphp.png')) ?>">
                </a>
            </div>
        </div>
        <div id="kodoc-content">
            <div class="wrapper">
                <div class="container">
                    <?php if (count($breadcrumb) > 1): ?>
                        <div class="span-22 prefix-1 suffix-1">
                            <ul id="kodoc-breadcrumb">
                                <?php foreach ($breadcrumb as $link => $title): ?>
                                    <?php if (is_string($link)): ?>
                                        <li><?= HTML::anchor($link, $title, null, null, true) ?></li>
                                    <?php else: ?>
                                        <li class="last"><?= $title ?></li>
                                    <?php endif ?>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endif ?>
                    <div class="span-6 prefix-1">
                        <div id="kodoc-topics">
                            <?= $menu ?>
                        </div>
                    </div>
                    <div id="kodoc-body" class="span-16 suffix-1 last">
                        <?= $content ?>
                    </div>
                </div>
            </div>
        </div>
        <div id="kodoc-footer">
            <div class="container">
                <div class="span-12">
                    <?php if (isset($copyright)): ?>
                        <p><?= $copyright ?></p>
                    <?php else: ?>
                        &nbsp;
                    <?php endif ?>
                </div>
                <div class="span-12 last right">
                    <p>Powered by <?= HTML::anchor('http://kilofox.net/', 'Bootphp') ?> v<?= Core::VERSION ?></p>
                </div>
            </div>
        </div>
    </body>
</html>
