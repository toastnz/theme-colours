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
Functions
------------------------------------------------------------------*/

import { getBrightess } from 'scripts/components/functions';

/*------------------------------------------------------------------
Document setup
------------------------------------------------------------------*/


// Observe the CMS for the themecolourpalette fieldsets
CMSObserver.observe('ul.themecolourpalette', (fieldsets) => {
  // Set up an object to store the theme colours
  window.ThemeColours = {};

  // Find the main cms element
  const main = document.querySelector('#Root_Main');

  // If the main element doesn't exist, return
  if (!main) return;

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

        const onChange = () => {
          // If the input is not checked, return
          if (!input.checked) return;
          // Get the brightness attribute from the input
          const brightnessAttribute = input.getAttribute('data-brightness');
          // Calculate the brightness
          const brightness = (brightnessAttribute) ? (brightnessAttribute === 'light') ? 255 : 0 : getBrightess(value);

          window.ThemeColours[name] = {
            value,
            brightness: (brightness > 130) ? 'light' : 'dark',
          };

          // fire a window event and pass the value and the brightness
          window.dispatchEvent(new CustomEvent('ThemeColourChange', { detail: { input, name, value, brightness: (brightness > 130) ? 'light' : 'dark' } }));
        };

        // Add an event listener to the input
        input.addEventListener('change', onChange);

        // Call the onChange function
        onChange();
      }
    }
  })();
});
