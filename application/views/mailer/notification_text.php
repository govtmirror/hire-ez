<?php $parsed = $notification->parsed(); ?>

<?= e($parsed["subject"]) ?>

View your notifications on EasyBid: <?= route('notifications') ?>

<?= __('r.email_signature_text') ?>