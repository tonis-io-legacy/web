The recommended way to get a Tonis object is by using the `Tonis\Web\Factory\TonisFactory`. The factory has several methods
available that will setup Tonis based on your application needs. For example, `createApi()` will attach subscribers
suited towards an API while `createWeb()` will attach subscribers more suited for a generic web application.

All factory methods accept a single `$config` array which will configure your Tonis instance. Internally, the array gets
converted into an instance of  `Tonis\Web\TonisConfig`.
