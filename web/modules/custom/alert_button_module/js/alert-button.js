((Drupal, once) => {
    Drupal.behaviors.alertButtonBehavior = {
      attach: (context) => {
        once('alertButtonBehavior', '#alert-button', context).forEach((element) => {
          element.addEventListener('click', () => {
            alert('Button clicked!');
          });
        });
      },
    };
  })(Drupal, once);
