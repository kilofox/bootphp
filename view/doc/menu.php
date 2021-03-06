<h2>Modules</h2>
<?php if (!empty($modules)): ?>
    <ul>
        <?php foreach ($modules as $url => $options): ?>
            <li><?= html::anchor(Route::get('docs/guide')->uri(array('module' => $url)), $options['name'], null, null, true) ?></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p class="error">No modules.</p>
<?php endif; ?>
