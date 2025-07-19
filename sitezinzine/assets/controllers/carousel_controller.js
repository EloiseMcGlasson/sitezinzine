import { Controller } from '@hotwired/stimulus';
import Glide from '@glidejs/glide';

export default class extends Controller {
  connect() {
    if (!this.element.classList.contains('glide')) return;

    this.glide = new Glide(this.element, {
      type: 'carousel',
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
    });

    this.glide.mount();
  }

  disconnect() {
    if (this.glide) {
      this.glide.destroy();
    }
  }
}
