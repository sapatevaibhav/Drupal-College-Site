# Vaibhav Event API

## Overview
The **Vaibhav Event API** is a custom Drupal 11 module that demonstrates how to dispatch and subscribe to custom events within Drupal's event system.

## Features
- Defines a custom event (`MyCustomEvent`).
- Dispatches the event from a service (`EventDispatcherService`).
- Listens to the event using an event subscriber (`MyEventSubscriber`).
- Logs event messages when triggered.

## Installation
1. Place the module inside the `modules/custom/vaibhav_event_api` directory.
2. Enable the module using Drush:
   ```sh
   ddev drush en vaibhav_event_api -y
   ```
3. Clear the cache to ensure services are registered properly:
   ```sh
   ddev drush cr
   ```

## Services
### Event Dispatcher Service
- **Service ID:** `vaibhav_event_api.event_dispatcher_service`
- **Class:** `Drupal\vaibhav_event_api\Service\EventDispatcherService`
- **Usage:** Dispatches `MyCustomEvent` with a custom message.

#### Dispatching an Event
Run the following command in Drush:
```php
$dispatcher = \Drupal::service('vaibhav_event_api.event_dispatcher_service');
$dispatcher->dispatchCustomEvent('Event triggered from Drush!');
```

## Event Subscriber
### MyEventSubscriber
- **Class:** `Drupal\vaibhav_event_api\EventSubscriber\MyEventSubscriber`
- **Listens to:** `vaibhav_event_api.custom_event`
- **Logs event details when triggered.**

## Testing the Module
### 1. Test via Drush
Run:
```sh
ddev drush php
```
Then dispatch an event:
```php
$dispatcher = \Drupal::service('vaibhav_event_api.event_dispatcher_service');
$dispatcher->dispatchCustomEvent('Test Event Triggered!');
```
Check logs:
```sh
ddev drush ws
```

### 2. Test via Web Route
1. Add the following route in `vaibhav_event_api.routing.yml`:
   ```yaml
   vaibhav_event_api.test_event:
     path: '/test-event'
     defaults:
       _controller: '\Drupal\vaibhav_event_api\Controller\EventTestController::triggerEvent'
       _title: 'Test Event Dispatch'
     requirements:
       _permission: 'access content'
   ```
2. Visit: [http://your-site.local/test-event](http://your-site.local/test-event)

If successful, the event will be dispatched and logged.

## Debugging
If the event subscriber does not trigger:
1. **Check if the event is registered:**
   ```sh
   ddev drush ev 'dump(\Drupal::service("event_dispatcher")->getListeners());'
   ```
2. **Ensure the subscriber is properly tagged in `services.yml`.**
3. **Clear cache and retry:**
   ```sh
   ddev drush cr
   ```

## License
This module is open-source and available for customization.
