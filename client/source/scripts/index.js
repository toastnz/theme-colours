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

const getBrightess = (color) => {
  // Convert the color to an array
  const rgb = color.replace(/[^\d,]/g, '').split(',');
  // Calculate the brightness
  const brightness = Math.round(((parseInt(rgb[0]) * 299) + (parseInt(rgb[1]) * 587) + (parseInt(rgb[2]) * 114)) / 1000);

  return brightness;
}

const calculateColorContrast = (color1, color2) => {
  // Helper function to convert hex to RGB
  const hexToRGB = (hex) => {
    let r = parseInt(hex.substring(1, 3), 16);
    let g = parseInt(hex.substring(3, 5), 16);
    let b = parseInt(hex.substring(5, 7), 16);
    return { r, g, b };
  }

  // Helper function to calculate luminance
  const calculateLuminance = (rgb) => {
    const { r, g, b } = rgb;
    const sRGB = [r, g, b].map(value => {
      value /= 255;
      return value <= 0.03928 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
    });
    return 0.2126 * sRGB[0] + 0.7152 * sRGB[1] + 0.0722 * sRGB[2];
  }

  // Calculate color contrast ratio
  const rgb1 = hexToRGB(color1);
  const rgb2 = hexToRGB(color2);
  const luminance1 = calculateLuminance(rgb1);
  const luminance2 = calculateLuminance(rgb2);
  const contrastRatio = (Math.max(luminance1, luminance2) + 0.05) / (Math.min(luminance1, luminance2) + 0.05);

  // Determine accessibility score
  if (contrastRatio >= 7) {
    return "AAA";
  } else if (contrastRatio >= 4.5) {
    return "AA";
  } else if (contrastRatio >= 3) {
    return "AA Large";
  } else {
    return "Fail";
  }
}

/*------------------------------------------------------------------
Document setup
------------------------------------------------------------------*/

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

// Look for the theme colour inputs
CMSObserver.observe('#Form_ItemEditForm_Colour', (inputs) => {
  // Grab the colour input
  const input = inputs[0];

  // Next find the inputs inside of this element #Form_ItemEditForm_ThemeColourTextColour
  const examples = [...document.querySelectorAll('#Form_ItemEditForm_ThemeColourTextColour input')];

  // When the input changes
  const onChange = () => {
    if (input.value == '') return;

    examples.forEach((example) => {
      // Find the parent (the label)
      const parent = example.parentNode;
      // Get the text colour hex value
      const textColour = (example.value == 'dark') ? '#ffffff' : '#000000';
      // Get the background colour hex value
      const backgroundColour = '#' + input.value;
      // Get the score
      const score = calculateColorContrast(backgroundColour, textColour);
      // Set the background colour of the parent to the input's value
      parent.style.backgroundColor = backgroundColour;

      // Set an attribute on the parent to show the accessibility score
      parent.setAttribute('data-contrast', 'Accessibility score: ' + score);
    });
  };

  // Create a new mutation observer to watch for changes to the input
  const Observer = new MutationObserver(() => onChange());

  examples.forEach((input) => {
    input.addEventListener('change', () => {
      if (input.parentNode.getAttribute('data-contrast').indexOf('Fail') >= 0) {
        alert('The contrast ratio between the text colour and the background colour is too low to pass accessibility standards. It is recommended to select a different text colour.');
      }
    });
  });

  // Observe the input for attribute changes
  Observer.observe(input, { attributes: true, });

  // Call the onChange function
  onChange();
});
