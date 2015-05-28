<p class="lead">An invalid result was returned from the dispatch action.</p>

<?php $this->insert('error::error-exception', ['exception' => $exception]);?>

<h2>Suggested Fix</h2>
<p>
    The most common reason for this is that your route maps to an invalid dispatchable. Ensure your
    route for <strong><?=$this->e($path);?></strong> has a correctly defined callable.
</p>
