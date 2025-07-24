import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = ['emission', 'dropzone']

    connect() {
        this.emissionTargets.forEach(emission => {
            emission.setAttribute('draggable', true)
            emission.addEventListener('dragstart', this.handleDragStart.bind(this))
        })

        this.dropzoneTargets.forEach(dropzone => {
            dropzone.addEventListener('dragover', this.handleDragOver.bind(this))
            dropzone.addEventListener('drop', this.handleDrop.bind(this))
            dropzone.addEventListener('dragleave', this.handleDragLeave.bind(this))
        })
    }

    handleDragStart(e) {
        e.dataTransfer.setData('text/plain', e.target.outerHTML)
        e.dataTransfer.effectAllowed = 'move'
    }

    handleDragOver(e) {
        e.preventDefault()
        e.currentTarget.classList.add('drag-over')
    }

    handleDragLeave(e) {
        e.currentTarget.classList.remove('drag-over')
    }

    handleDrop(e) {
        e.preventDefault()
        const data = e.dataTransfer.getData('text/plain')
        e.currentTarget.innerHTML = data
        e.currentTarget.classList.remove('drag-over')

        const newBlock = e.currentTarget.querySelector('[data-grid-target="emission"]')
        newBlock.setAttribute('draggable', true)
        newBlock.addEventListener('dragstart', this.handleDragStart.bind(this))
    }
}