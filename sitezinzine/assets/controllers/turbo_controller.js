import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */


export default class extends Controller {
    static values = {
        playing: Boolean,
        volume: Number
    }

    connect() {
        this.audio = this.element.querySelector('#audio-player');
        
        // Restaurer l'état si existant
        const wasPlaying = localStorage.getItem('audioWasPlaying') === 'true';
        const savedVolume = parseFloat(localStorage.getItem('audioVolume') || 1);

        if (this.audio) {
            this.audio.volume = savedVolume;
            if (wasPlaying) {
                this.audio.play();
            }
        }

        // Écouter les événements de navigation
        document.documentElement.addEventListener('turbo:load', this._handleTurboLoad);
        document.documentElement.addEventListener('turbo:visit', this._handleTurboVisit);
    }

    disconnect() {
        document.documentElement.removeEventListener('turbo:load', this._handleTurboLoad);
        document.documentElement.removeEventListener('turbo:visit', this._handleTurboVisit);
    }

    _handleTurboVisit = () => {
        if (this.audio) {
            localStorage.setItem('audioWasPlaying', !this.audio.paused);
            localStorage.setItem('audioVolume', this.audio.volume);
        }
    }

    _handleTurboLoad = () => {
        // Réinitialiser la référence audio après la navigation
        this.audio = document.querySelector('#audio-player');
        if (this.audio && localStorage.getItem('audioWasPlaying') === 'true') {
            this.audio.volume = parseFloat(localStorage.getItem('audioVolume') || 1);
            this.audio.play();
        }
    }
}
