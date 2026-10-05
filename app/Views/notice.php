<?php
/**
 * Cette view permet d'afficher une notification
 * Elle dispose d'un type (error, warning, info, success)
 * D'un titre général et des détails sur le message à afficher.
 *
 */
//$type = $noticeType ?? '';
//$titre = $noticeTitre ?? '';
//$details = $noticeDetails ?? '';
$notice = session()->getFlashdata('notice');
//var_dump($notice); // debug
$type = $notice['type'] ?? '';
$titre = $notice['titre'] ?? '';
$details = $notice['details'] ?? '';
$errors = $notice['errors'] ?? null;
if (!empty($type)): ?>
    <div class='notification notification--<?= strtolower($type) ?>' role="alert">
        <!-- Bouton de fermeture -->
        <button class="notification__close"
                onclick="this.parentElement.classList.add('notification--closing'); setTimeout(() => this.parentElement.remove(), 300);"
                aria-label="Fermer la notification"
                type="button">
        </button>

        <h3><?= esc($titre) ?></h3>

        <?php if (! empty($details)) :
            if (is_array($details)): ?>
                <ul class='notification__errors'>
                    <?php foreach ($details as $field => $error): ?>
                        <li>
                            <?php if (is_string($field) && ! is_numeric($field)): ?>
                            <?php endif; ?>
                            <?= esc($error) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

            <?php else : ?>
                <p class='notification__errors'>
                    <?= $details ?>
                </p>
            <?php endif;
        endif; ?>
    </div>
<?php endif; ?>