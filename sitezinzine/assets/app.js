
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/reset.css';
import './styles/app.css';

import { Application } from '@hotwired/stimulus';
import { definitionsFromContext } from '@hotwired/stimulus-webpack-helpers';

const application = Application.start();
const context = require.context('./controllers', true, /\.js$/);
application.load(definitionsFromContext(context));

import '@hotwired/turbo';
import './controllers';

// (glidejs, animations, autres comportements ici…)

console.log('Stimulus loaded successfully 🎉');




// On attend que Turbo ait fini de charger la page
document.addEventListener('turbo:load', () => {
  // Initialiser Glide sur tous les éléments .glide présents
  document.querySelectorAll('.glide').forEach(el => {
    // Attention à ne pas monter plusieurs fois le même slider, à gérer si besoin
    new Glide(el, {
      type: "carousel",
      perView: 4,
      gap: 20,
      animationDuration: 800,
      autoplay: false,
      hoverpause: true,
      bound: true,
      breakpoints: {
        1024: { perView: 3 },
        768: { perView: 2 },
        480: { perView: 1 }
      }
    }).mount();
  });
});




console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
