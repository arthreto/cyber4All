<?php $pager->setSurroundCount(2); ?>

<nav aria-label="<?= lang('Pager.pageNavigation') ?>">
    <ul class="pagination">
        <?php if ($pager->hasPrevious()): ?>
            <li><a href="<?= esc($pager->getFirst()) ?>">1</a></li>
        <?php endif; ?>

        <?php foreach ($pager->links() as $link): ?>
            <li <?= $link['active'] ? 'class="active"' : '' ?>>
                <a href="<?= esc($link['uri']) ?>"><?= $link['title'] ?></a>
            </li>
        <?php endforeach; ?>

        <?php if ($pager->hasNext()): ?>
            <li><a href="<?= esc($pager->getNext()) ?>" aria-label="<?= lang('Pager.next') ?>"><?= lang('Pager.next') ?></a></li>
            <li><a href="<?= esc($pager->getLast()) ?>" aria-label="<?= lang('Pager.last') ?>"><?= lang('Pager.last') ?></a></li>
        <?php endif; ?>
    </ul>
</nav>