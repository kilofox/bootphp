<?php
namespace App\view\doc;

use Bootphp\HTML;
?>
<?php if (is_array($array)): ?>
    <div class="page-toc">
        <?php foreach ($array as $item): ?>
            <?php if ($item['level'] > 1): ?>
                <?= str_repeat('&nbsp;', ($item['level'] - 1) * 4) ?>
            <?php endif; ?>
            <?= HTML::anchor('#' . $item['id'], $item['name'], null, null, true) ?><br>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
