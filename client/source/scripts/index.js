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

const getBrightess = (color) => {
  // Convert the color to an array
  const rgb = color.replace(/[^\d,]/g, '').split(',');
  // Calculate the brightness
  const brightness = Math.round(((parseInt(rgb[0]) * 299) + (parseInt(rgb[1]) * 587) + (parseInt(rgb[2]) * 114)) / 1000);

  return brightness;
}

// Observe the CMS for the themecolourpalette fieldsets
CMSObserver.observe('.themecolourpalette', (fieldsets) => {
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
          const brightness = (brightnessAttribute) ? (brightnessAttribute === 'light') ? 255 : 0 : getBrightess(colour);

          // Set the value as a CSS variable on the body
          document.body.style.setProperty(`--ThemeColours-${name}`, value);

          // Assign a text colour based on the brightness
          if (brightness < 130) {
            document.body.style.setProperty(`--ThemeColours-${name}_Text`, '#fff');
          } else {
            document.body.style.setProperty(`--ThemeColours-${name}_Text`, '#000');
          }

          if (value === 'rgba(0, 0, 0, 0)') {
            // Remove the style properties
            document.body.style.removeProperty(`--ThemeColours-${name}`);
            document.body.style.removeProperty(`--ThemeColours-${name}_Text`);
          }
        };

        // Add an event listener to the input
        input.addEventListener('change', onChange);

        // Call the onChange function
        onChange();
      }
    }
  })();
});
