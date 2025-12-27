import { Controller } from "@hotwired/stimulus";
import { Turbo } from "@hotwired/turbo";

export default class extends Controller {
  static targets = ["form"];
  static values = { url: String };

  reset(event) {
    event.preventDefault();

    if (!this.hasFormTarget) return;

    // 1) reset HTML
    this.formTarget.reset();

    // 2) reset flatpickr (si présent)
    this.formTarget.querySelectorAll(".flatpickr-input").forEach((input) => {
      if (input && input._flatpickr) {
        input._flatpickr.clear();
      }
    });

    // 3) revenir à la page “propre” (sans query params)
    const url = this.hasUrlValue ? this.urlValue : window.location.pathname;
    Turbo.visit(url);
  }
}
