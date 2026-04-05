import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'recurrenceType',
        'monthlyOccurrenceWrapper',
        'monthlyOccurrence',
        'monthIntervalWrapper',
        'monthInterval',
        'weekOffsetWrapper',
        'weekOffset',
    ];

    connect() {
        this.toggleRecurrenceFields();
    }

    toggleRecurrenceFields() {
        const isMonthly = this.recurrenceTypeTarget.value === 'monthly';

        if (this.hasMonthlyOccurrenceWrapperTarget) {
            this.monthlyOccurrenceWrapperTarget.style.display = isMonthly ? 'block' : 'none';
        }

        if (this.hasMonthIntervalWrapperTarget) {
            this.monthIntervalWrapperTarget.style.display = isMonthly ? 'block' : 'none';
        }

        if (this.hasWeekOffsetWrapperTarget) {
            this.weekOffsetWrapperTarget.style.display = isMonthly ? 'none' : 'block';
        }

        if (isMonthly) {
            if (this.hasWeekOffsetTarget) {
                this.weekOffsetTarget.value = '0';
            }
        } else {
            if (this.hasMonthlyOccurrenceTarget) {
                this.monthlyOccurrenceTarget.value = '';
            }

            if (this.hasMonthIntervalTarget) {
                this.monthIntervalTarget.value = '1';
            }
        }
    }
}