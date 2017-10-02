<?php
namespace Bootphp\Exception\view;

use Bootphp\Core;
use Bootphp\Debug;

// Unique error identifier
$errorId = uniqid('error');
?>
<style>
    #bootphp_error { background: #ddd; font-size: 1em; font-family:sans-serif; text-align: left; color: #111; }
    #bootphp_error h1,
    #bootphp_error h2 { margin: 0; padding: 1em; font-size: 1em; font-weight: normal; background: #911; color: #fff; }
    #bootphp_error h1 a,
    #bootphp_error h2 a { color: #fff; }
    #bootphp_error h2 { background: #222; }
    #bootphp_error h3 { margin: 0; padding: 0.4em 0 0; font-size: 1em; font-weight: normal; }
    #bootphp_error p { margin: 0; padding: 0.2em 0; }
    #bootphp_error a { color: #1b323b; }
    #bootphp_error pre { overflow: auto; white-space: pre-wrap; }
    #bootphp_error table { width: 100%; display: block; margin: 0 0 0.4em; padding: 0; border-collapse: collapse; background: #fff; }
    #bootphp_error table td { border: solid 1px #ddd; text-align: left; vertical-align: top; padding: 0.4em; }
    #bootphp_error div.content { padding: 0.4em 1em 1em; overflow: hidden; }
    #bootphp_error pre.source { margin: 0 0 1em; padding: 0.4em; background: #fff; border: dotted 1px #b7c680; line-height: 1.2em; }
    #bootphp_error pre.source span.line { display: block; }
    #bootphp_error pre.source span.highlight { background: #f0eb96; }
    #bootphp_error pre.source span.line span.number { color: #666; }
    #bootphp_error ol.trace { display: block; margin: 0 0 0 2em; padding: 0; list-style: decimal; }
    #bootphp_error ol.trace li { margin: 0; padding: 0; }
    .js .collapsed { display: none; }
</style>
<script>
	document.documentElement.className = document.documentElement.className + ' js';
	function toggle(elem)
	{
		elem = document.getElementById(elem);

		if (elem.style && elem.style['display'])
			// Only works with the "style" attr
			var disp = elem.style['display'];
		else if (elem.currentStyle)
			// For MSIE, naturally
			var disp = elem.currentStyle['display'];
		else if (window.getComputedStyle)
			// For most other browsers
			var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');

		// Toggle the state of the "display" style
		elem.style.display = disp == 'block' ? 'none' : 'block';
		return false;
	}
</script>
<div id="bootphp_error">
    <h1><span class="type"><?= $class ?> [ <?= $code ?> ]:</span> <span class="message"><?= htmlspecialchars((string) $message, ENT_QUOTES | ENT_IGNORE, 'utf-8', true); ?></span></h1>
    <div id="<?= $errorId ?>" class="content">
        <p><span class="file"><?= Debug::path($file) ?> [ <?= $line ?> ]</span></p>
        <?= Debug::source($file, $line) ?>
        <ol class="trace">
            <?php foreach (Debug::trace($trace) as $i => $step): ?>
                <li>
                    <p>
                        <span class="file">
                            <?php if ($step['file']): $source_id = $errorId . 'source' . $i; ?>
                                <a href="#<?= $source_id ?>" onclick="return toggle('<?= $source_id ?>')"><?= Debug::path($step['file']) ?> [ <?= $step['line'] ?> ]</a>
                            <?php else: ?>
                                {PHP internal call}
                            <?php endif ?>
                        </span>
                        &raquo;
                        <?= $step['function'] ?>(<?php if ($step['args']): $args_id = $errorId . 'args' . $i; ?><a href="#<?= $args_id ?>" onclick="return toggle('<?= $args_id ?>')">arguments</a><?php endif ?>)
                    </p>
                    <?php if (isset($args_id)): ?>
                        <div id="<?= $args_id ?>" class="collapsed">
                            <table cellspacing="0">
                                <?php foreach ($step['args'] as $name => $arg): ?>
                                    <tr>
                                        <td><code><?= $name ?></code></td>
                                        <td><pre><?= Debug::dump($arg) ?></pre></td>
                                    </tr>
                                <?php endforeach ?>
                            </table>
                        </div>
                    <?php endif ?>
                    <?php if (isset($source_id)): ?>
                        <pre id="<?= $source_id ?>" class="source collapsed"><code><?= $step['source'] ?></code></pre>
                    <?php endif ?>
                </li>
                <?php unset($args_id, $source_id); ?>
            <?php endforeach ?>
        </ol>
    </div>
    <h2><a href="#<?= $env_id = $errorId . 'environment' ?>" onclick="return toggle('<?= $env_id ?>')">Environment</a></h2>
    <div id="<?= $env_id ?>" class="content collapsed">
        <?php $included = get_included_files() ?>
        <h3><a href="#<?= $env_id = $errorId . 'environment_included' ?>" onclick="return toggle('<?= $env_id ?>')">Included files</a> (<?= count($included) ?>)</h3>
        <div id="<?= $env_id ?>" class="collapsed">
            <table cellspacing="0">
                <?php foreach ($included as $file): ?>
                    <tr>
                        <td><code><?= Debug::path($file) ?></code></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
        <?php $included = get_loaded_extensions() ?>
        <h3><a href="#<?= $env_id = $errorId . 'environment_loaded' ?>" onclick="return toggle('<?= $env_id ?>')">Loaded extensions</a> (<?= count($included) ?>)</h3>
        <div id="<?= $env_id ?>" class="collapsed">
            <table cellspacing="0">
                <?php foreach ($included as $file): ?>
                    <tr>
                        <td><code><?= Debug::path($file) ?></code></td>
                    </tr>
                <?php endforeach ?>
            </table>
        </div>
        <?php foreach (['_SESSION', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER'] as $var): ?>
            <?php if (empty($GLOBALS[$var]) || !is_array($GLOBALS[$var])) continue ?>
            <h3><a href="#<?= $env_id = $errorId . 'environment' . strtolower($var) ?>" onclick="return toggle('<?= $env_id ?>')">$<?= $var ?></a></h3>
            <div id="<?= $env_id ?>" class="collapsed">
                <table cellspacing="0">
                    <?php foreach ($GLOBALS[$var] as $key => $value): ?>
                        <tr>
                            <td><code><?= htmlspecialchars((string) $key, ENT_QUOTES, 'utf-8', true); ?></code></td>
                            <td><pre><?= Debug::dump($value) ?></pre></td>
                        </tr>
                    <?php endforeach ?>
                </table>
            </div>
        <?php endforeach ?>
    </div>
</div>
