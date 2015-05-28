<?php $this->layout('layout::layout'); ?>

<?php $this->start('body');?>
    <div class="page-header">
        <h1>500 <small>application error</small></h1>
    </div>

    <?php if (isset($type)) : ?>
        <?php $this->insert('error::error-' . $type, $this->data);?>
    <?php endif; ?>
<?php $this->stop(); ?>
