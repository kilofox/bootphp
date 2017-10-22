<?php
use Bootphp\HTML;
use Bootphp\Route;
use Bootphp\Core;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?= $title ?> | Bootphp <?= 'User Guide'; ?></title>
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
                <div id="kodoc-menu">
                    <ul>
                        <li class="guide first">
                            <a href="<?= Route::url('doc/guide') ?>">User Guide</a>
                        </li>
                        <?php if (Core::$config->load('userguide.api_browser') === true): ?>
                            <li class="api">
                                <a href="<?= Route::url('doc/api') ?>">API Browser</a>
                            </li>
                        <?php endif ?>
                    </ul>
                </div>
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
                            <h2>Modules</h2>
                            <?php if (!empty($modules)): ?>
                                <ul>
                                    <?php foreach ($modules as $url => $options): ?>
                                        <li><?= html::anchor(Route::get('doc/guide')->uri(array('module' => $url)), $options['name'], null, null, true) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="error">No modules.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div id="kodoc-body" class="span-16 suffix-1 last">
                        <?= $content ?>
                        <?php if ($show_comments): ?>
                            <div id="disqus_thread" class="clear"></div>
                            <script>
                                var disqus_identifier = '<?= HTML::chars(Request::current()->uri()) ?>';
                                $(function() {
                                    var dsq = document.createElement('script');
                                    dsq.type = 'text/javascript';
                                    dsq.async = true;
                                    dsq.src = 'http://bootphp.disqus.com/embed.js';
                                    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
                                });
                            </script>
                            <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript=bootphp">comments powered by Disqus.</a></noscript>
                            <a href="http://disqus.com" class="dsq-brlink">Documentation comments powered by <span class="logo-disqus">Disqus</span></a>
                        <?php endif ?>
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
        <?php if (Core::$environment === Core::PRODUCTION): ?>
            <script>
                $(function() {
                    var links = document.getElementsByTagName('a');
                    var query = '?';
                    for (var i = 0; i < links.length; i++) {
                        if (links[i].href.indexOf('#disqus_thread') >= 0) {
                            query += 'url' + i + '=' + encodeURIComponent(links[i].href) + '&';
                        }
                    }
                    document.write('<script src="http://disqus.com/forums/bootphp/get_num_replies.js' + query + '"></' + 'script>');
                });
            </script>
        <?php endif ?>
    </body>
</html>
