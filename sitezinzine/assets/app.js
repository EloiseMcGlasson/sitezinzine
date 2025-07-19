import './styles/reset.css';
import './styles/app.css';

import '@hotwired/turbo';
import { Application } from '@hotwired/stimulus';
import { definitionsFromContext } from '@hotwired/stimulus-webpack-helpers';

// Initialise Stimulus
const application = Application.start();

// Charge automatiquement tous les contrôleurs depuis /controllers
const context = require.context('./controllers', true, /\.js$/);
application.load(definitionsFromContext(context));

console.log('Stimulus & Turbo loaded successfully 🎉');
