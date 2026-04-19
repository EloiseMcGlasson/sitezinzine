import { Controller } from "@hotwired/stimulus";
import * as Turbo from "@hotwired/turbo";

export default class extends Controller {
  static targets = ["form"];
  static values = { url: String };

  reset(event) {
    event.preventDefault();

    if (!this.hasFormTarget) {
      return;
    }

    // 1) reset HTML natif
    this.formTarget.reset();

    // 2) reset flatpickr sur les vrais inputs d'origine
    this.formTarget
      .querySelectorAll('[data-controller~="flatpickr"]')
      .forEach((input) => {
        if (input._flatpickr) {
          input._flatpickr.clear();
        }
      });

    // 3) revenir à la page propre
    const url = this.hasUrlValue ? this.urlValue : window.location.pathname;
    Turbo.visit(url);
  }
}