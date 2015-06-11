<p class="lead">No template was available.</p>

<h2>Suggested Fix</h2>
<p>
    The most common reason for this is that your view model did not return a template that was renderable. You can
    manually set the template on the ViewModel as the first argument.
</p>
<pre>
    function () {
        return new ViewModel('my-template', ['foo' => 'bar']);
    }
</pre>
