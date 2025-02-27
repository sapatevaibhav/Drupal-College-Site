(function (Drupal, drupalSettings) {
    Drupal.behaviors.mySettingsExample = {
      attach: function (context, settings) {
        // Log the greeting from drupalSettings
        console.log(drupalSettings.my_module.greeting);
      }
    };
  })(Drupal, drupalSettings);
