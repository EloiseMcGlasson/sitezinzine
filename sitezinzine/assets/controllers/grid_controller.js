import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = ['emission', 'dropzone']

    connect() {
        this.tooltip = document.getElementById('tooltip')

        // Événements pour les émissions disponibles
        this.emissionTargets.forEach(emission => {
            emission.setAttribute('draggable', true)
            emission.addEventListener('dragstart', this.handleDragStart.bind(this))
        })

        // Événements pour les dropzones
        this.dropzoneTargets.forEach(dropzone => {
            dropzone.addEventListener('dragover', this.handleDragOver.bind(this))
            dropzone.addEventListener('drop', this.handleDrop.bind(this))
            dropzone.addEventListener('dragleave', this.handleDragLeave.bind(this))
        })

        // Ajoute les tooltips aux émissions déjà dans la grille (si existantes)
        document.querySelectorAll('.emission-block.in-grid').forEach(block => {
            this.setupTooltipListeners(block)
        })
    }

    setupTooltipListeners(element) {
        element.addEventListener('mouseover', (e) => this.handleHoverElement(e))
        element.addEventListener('mousemove', (e) => this.moveTooltip(e))
        element.addEventListener('mouseout', () => this.hideTooltip())
    }

    handleHoverElement(e) {
        const text = e.currentTarget.innerText.trim()
        if (!text) return

        this.tooltip.innerText = text
        this.tooltip.style.display = 'block'
    }

    moveTooltip(e) {
        this.tooltip.style.top = `${e.clientY + 12}px`
        this.tooltip.style.left = `${e.clientX + 12}px`
    }

    hideTooltip() {
        this.tooltip.style.display = 'none'
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
        e.currentTarget.classList.remove('drag-over')

        const dropzone = e.currentTarget
        const dateStr = dropzone.dataset.date
        const startDate = new Date(dateStr)

        const data = e.dataTransfer.getData('text/plain')
        const temp = document.createElement('div')
        temp.innerHTML = data
        const emissionElement = temp.firstElementChild

        const duration = parseInt(emissionElement.dataset.duration) || 15
        const heightPer15Min = 8
        const calculatedHeight = (duration / 15) * heightPer15Min
        const cellsToCover = Math.ceil(duration / 15)

        const allDropzones = this.dropzoneTargets

        function formatLocalDate(date) {
            const yyyy = date.getFullYear()
            const mm = String(date.getMonth() + 1).padStart(2, '0')
            const dd = String(date.getDate()).padStart(2, '0')
            const hh = String(date.getHours()).padStart(2, '0')
            const min = String(date.getMinutes()).padStart(2, '0')
            return `${yyyy}-${mm}-${dd} ${hh}:${min}:00`
        }

        // Vérifie occupation
        for (let i = 0; i < cellsToCover; i++) {
            const futureDate = new Date(startDate.getTime() + i * 15 * 60000)
            const targetStr = formatLocalDate(futureDate)
            const dz = allDropzones.find(el => el.dataset.date === targetStr)
            if (!dz || dz.classList.contains('occupied')) {
                console.warn('Cellule occupée, drop refusé')
                return
            }
        }

        // Supprime contenu actuel
        dropzone.innerHTML = ''

        // Crée le bloc émission
        const bloc = document.createElement('div')
        bloc.classList.add('emission-block', 'in-grid')
        bloc.textContent = emissionElement.textContent.trim()
        bloc.dataset.id = emissionElement.dataset.id
        bloc.dataset.duration = duration
        bloc.style.height = `${calculatedHeight}px`
        bloc.setAttribute('draggable', true)
        bloc.addEventListener('dragstart', this.handleDragStart.bind(this))

        // Active le tooltip sur ce bloc
        this.setupTooltipListeners(bloc)

        // Ajoute à la grille
        dropzone.appendChild(bloc)

        // Marque cellules comme occupées
        for (let i = 0; i < cellsToCover; i++) {
            const futureDate = new Date(startDate.getTime() + i * 15 * 60000)
            const targetStr = formatLocalDate(futureDate)
            const dz = allDropzones.find(el => el.dataset.date === targetStr)
            if (dz) dz.classList.add('occupied')
        }
    }
}
