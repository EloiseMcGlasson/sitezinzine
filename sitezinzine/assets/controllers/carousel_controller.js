import { Controller } from '@hotwired/stimulus';
import Glide from '@glidejs/glide';

export default class extends Controller {
  connect() {
    if (!this.element.classList.contains('glide')) return;

    // Évite les doubles mounts si Turbo/Stimulus reconnecte
    if (this.glide) return;

    this.glide = new Glide(this.element, {
      type: 'carousel',
      focusAt: 'center',
      gap: 20,
      perView: 4,
      animationDuration: 800,
      autoplay: false,
      hoverpause: true,
      bound: true,

      // ✅ Mobile: 1 carte + aperçu de la suivante (plus joli)
      // ✅ Landscape: on remonte à 2 si écran peu haut
      breakpoints: {
        1200: { perView: 3 },
        1024: { perView: 2 },

        // Mobile/tablette petite largeur
        768: { perView: 1, focusAt: 0, peek: { before: 0, after: 110 }, gap: 14 },

        // Très petit
        480: { perView: 1, focusAt: 0, peek: { before: 0, after: 90 }, gap: 12 }
      }
    });

    this.glide.mount();
  }

  disconnect() {
    if (this.glide) {
      this.glide.destroy();
      this.glide = null;
    }
  }
}
