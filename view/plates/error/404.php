<?php $this->layout('layout::layout'); ?>

<?php $this->start('body');?>
    <div class="page-header">
        <h1>404 <small><?=isset($type) ? $type : 'page';?> not found</small></h1>
    </div>

    <?php if (isset($type)) : ?>
        <?=$this->insert('error::404-' . $type, $this->data);?>
    <?php endif;?>
<?php $this->stop();?>
