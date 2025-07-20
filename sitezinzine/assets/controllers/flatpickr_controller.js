import { Controller } from '@hotwired/stimulus';
import flatpickr from 'flatpickr';
import { French } from 'flatpickr/dist/l10n/fr.js';

export default class extends Controller {
  static values = {
    enableTime: Boolean,
    dateFormat: String,
    altFormat: String
  }

  connect() {
    flatpickr(this.element, {
      enableTime: this.enableTimeValue || false,
      dateFormat: this.dateFormatValue || 'Y-m-d',
      altInput: true,
      altFormat: this.altFormatValue || 'd/m/Y',
      locale: French,
      allowInput: true
    });
  }
}
