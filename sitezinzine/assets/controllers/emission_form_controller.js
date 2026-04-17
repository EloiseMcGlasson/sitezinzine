import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['categorie', 'editeur', 'duree']

  connect() {
    this.syncCategoryDefaults()
  }

  syncCategoryDefaults() {
    if (!this.hasCategorieTarget) {
      return
    }

    const selectedOption = this.categorieTarget.selectedOptions[0]

    if (!selectedOption) {
      return
    }

    const editeurId = selectedOption.dataset.editeurId || ''
    const duree = selectedOption.dataset.duree || ''

    if (this.hasEditeurTarget && editeurId !== '') {
      this.editeurTarget.value = editeurId
      this.dispatchNativeChange(this.editeurTarget)
    }

    if (this.hasDureeTarget && duree !== '') {
      this.dureeTarget.value = duree
      this.dispatchNativeChange(this.dureeTarget)
    }
  }

  dispatchNativeChange(element) {
    element.dispatchEvent(new Event('change', { bubbles: true }))
  }
}