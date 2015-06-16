<p class="lead">An exception occurred during dispatching.</p>

<?php if (isset($exception)) : ?>
<h2>Additional information:</h2>
<?php $this->insert('error::_exception', ['exception' => $exception]); ?>

<ul class="list-unstyled">
    <?php if ($exception->getPrevious()) : ?>
    <?php foreach ($exception->getPrevious() as $exception) : ?>
        <?php $this->insert('error::_exception', ['exception' => $exception]); ?>
    <?php endforeach; ?>
    <?php endif; ?>
</ul>
<?php endif; ?>
