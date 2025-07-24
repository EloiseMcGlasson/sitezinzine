import { Controller } from "@hotwired/stimulus"
import * as Turbo from "@hotwired/turbo"


export default class extends Controller {
    static values = {
        url: String
    }

    redirectWithReturnTo(event) {
        event.preventDefault()

        const currentUrl = window.location.pathname + window.location.search
        const target = `${this.urlValue}?returnTo=${encodeURIComponent(currentUrl)}`
        Turbo.visit(target)
    }
}
