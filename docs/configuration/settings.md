debug
-----

Sets debug mode for Tonis. Packages can read this to determine sane defaults for configuration. An example is
caching based on debug mode.

<dl>
  <dt>Type</dt>
  <dd>boolean</dd>

  <dt>Default</dt>
  <dd>false</dd>
</dl>

cache_dir
---------

Sets the cache directory for Tonis. Packages should read this to determine where to put cache files.

<dl>
  <dt>Type</dt>
  <dd>string</dd>

  <dt>Default</dt>
  <dd>null</dd>
</dl>

environment
-----------

Environment variables to inject. These can be retrieved using the PHP method `getenv()`.

<dl>
  <dt>Type</dt>
  <dd>array</dd>

  <dt>Default</dt>
  <dd>[]</dd>
</dl>

required_environment
--------------------

Environment variables to require. If they are not present in `environment` (above) then an exception will be thrown. 
Useful to ensure your application is in a proper state during run-time.

<dl>
  <dt>Type</dt>
  <dd>array</dd>

  <dt>Default</dt>
  <dd>['TONIS_DEBUG']</dd>
  
  <dt>Notes</dt>
  <dd>Tonis handles setting <code>TONIS_DEBUG</code> from the <code>debug</code> setting automatically.</dd>
</dl>

packages
--------

Packages to load. See the [Packages](/basics/packages) documentation for more information.

<dl>
  <dt>Type</dt>
  <dd>array</dd>

  <dt>Default</dt>
  <dd>[]</dd>
  
  <dt>Notes</dt>
  <dd>Tonis\Web is a package itself and is automatically loaded for you.</dd>
</dl>

subscribers
-----------

Subscribers to register. In generaly this is set for you by the `Tonis\Web\Factory\TonisFactory`. For most 
users this will not need to be modified.

<dl>
  <dt>Type</dt>
  <dd>array</dd>

  <dt>Default</dt>
  <dd>[]</dd>
</dl>
