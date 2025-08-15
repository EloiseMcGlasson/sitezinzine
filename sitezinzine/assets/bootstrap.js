// assets/bootstrap.js
import { Application } from '@hotwired/stimulus';
import * as Turbo from '@hotwired/turbo'; // turbo activé
import LiveController from '@symfony/ux-live-component/dist/live_controller';
import '@symfony/ux-live-component/dist/live.min.css';
import { definitionsFromContext } from '@hotwired/stimulus-webpack-helpers';

// Démarre Stimulus (sans stimulus-bridge)
const app = Application.start();

// Enregistre le contrôleur Live explicitement
app.register('live', LiveController);

// Auto-charge TES contrôleurs locaux (assets/controllers/**/*.js)
const context = require.context('./controllers', true, /\.js$/);
app.load(definitionsFromContext(context));

export default app;
