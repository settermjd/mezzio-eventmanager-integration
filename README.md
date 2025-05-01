<!-- markdownlint-disable MD013 -->
# Mezzio / Laminas EventManager Integration

This project provides a simplistic way of integrating laminas-eventmanager with Mezzio projects that are (originally) scaffolded with [the Mezzio Skeleton][mezzio-url], making it almost trivial to subscribe listeners to events.

> [!TIP]
> If you're new to Mezzio, check out [Mezzio Essentials][mezzioessentials-url] a practical, hands-on guide, which steps you through how to build an application, from start to finish.

## Prerequisites

To use this project, you need the following:

- Composer installed globally
- PHP 8.3 or 8.4

## Usage

To use the package with an existing Mezzio application, use Composer to add the package as a dependency to your project, as in the following example:

```bash
composer require mezzio-eventmanager-integration
```

Then, to subscribe listeners to events, you need to do two things:

1. Add a `listeners` element to the application's configuration, listing the listeners to subscribe to an event, and the [priority][laminas-eventmanager-priority-url] to subscribe them at.
   There are two things to be aware of here:
     - If you don't assign a priority, a listener will be subscribed with a default priority of **1**.
     - Higher priority values execute **earlier**.
      Lower (negative) priority values execute **later**.
1. The listeners must be registered as services in the DI container.

There are many ways to add a `listeners` element to the application's configuration, but likely the simplest is to create a new file in the _config/autoload_ directory named _listeners.global.php_, and in that file, add a configuration similar to the example below.

```php
<?php

return [
    'listeners' => [
        FakeLoggerListener::class       => [
            'event'    => 'test-event',
            'priority' => 10,
        ],
        FakeNotificationListener::class => [
            'event'    => 'test-event',
            'priority' => 20,
        ],
    ],
];
```

Assuming the configuration above, `FakeLoggerListener` and `FakeNotificationListener` are now listening for (or subscribed to) the "test-event" event.
When the event is triggered, `FakeLoggerListener` will execute first, then `FakeNotificationListener` will execute (assuming that execution isn't [short-circuited][laminas-eventmanager-shortcircuiting-url]).

## Contributing

If you want to contribute to the project, whether you have found issues with it or just want to improve it, here's how:

- [Issues][issues-url]: ask questions and submit your feature requests, bug reports, etc
- [Pull requests][prs-url]: send your improvements

## Did You Find The Project Useful?

If the project was useful, and you want to say thank you and/or support its active development, here's how:

- Add a GitHub Star to the project
- Write an interesting article about the project wherever you blog

## Disclaimer

No warranty expressed or implied. Software is as is.

[issues-url]: https://github.com/settermjd/mezzio-eventmanager-integration/issues/new/choose
[laminas-eventmanager-priority-url]: https://docs.laminas.dev/laminas-eventmanager/tutorial/#keeping-it-in-order
[laminas-eventmanager-shortcircuiting-url]: https://docs.laminas.dev/laminas-eventmanager/tutorial/#short-circuiting-listener-execution
[mezzio-url]: https://docs.mezzio.dev/mezzio/
[mezzioessentials-url]: https://mezzioessentials.com
[prs-url]: https://github.com/settermjd/mezzio-eventmanager-integration/pulls
<!-- markdownlint-enable MD013 -->
