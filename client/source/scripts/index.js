/*------------------------------------------------------------------
Import styles
------------------------------------------------------------------*/

import 'styles/index.scss';

/*------------------------------------------------------------------
Import modules
------------------------------------------------------------------*/

import DomObserverController from 'domobserverjs';

/*------------------------------------------------------------------
Dom Observer
------------------------------------------------------------------*/

const CMSObserver = new DomObserverController();

/*------------------------------------------------------------------
Document setup
------------------------------------------------------------------*/

// Observe the CMS for the toast-block-layouts fieldsets
CMSObserver.observe('.colorpalette', (fieldsets) => {
  // Loop through the fieldsets
  (async () => {
    for (const fieldset of fieldsets) {
      // Find all the inputs in the fieldset
      const inputs = fieldset.querySelectorAll('input');

      // Loop the images
      for (const input of inputs) {
        // Find the input label
        const label = input.labels[0];
        // Find the input name
        const name = input.name;

        // Get the computed background colour of the label as the value
        const value = window.getComputedStyle(label).backgroundColor;

        // Set the value as a CSS variable on the body
        document.body.style.setProperty(`--ThemeColours-${name}`, value);
      }
    }
  })();
});
