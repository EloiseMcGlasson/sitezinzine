import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = ['emission', 'dropzone']

    connect() {
        this.tooltip = document.getElementById('tooltip')
        this.draggedElement = null
        this.sourceDropzone = null
        this.pool = this.element.querySelector('#emissions-pool')

        // Événements sur les émissions
        this.emissionTargets.forEach(emission => {
            this.makeDraggable(emission)
        })

        // Événements sur les dropzones de grille
        this.dropzoneTargets.forEach(dropzone => {
            dropzone.addEventListener('dragover', this.handleDragOver.bind(this))
            dropzone.addEventListener('drop', this.handleDrop.bind(this))
            dropzone.addEventListener('dragleave', this.handleDragLeave.bind(this))
        })

        // Événements sur la zone de retour (pool)
        this.pool.addEventListener('dragover', this.handlePoolDragOver.bind(this))
        this.pool.addEventListener('dragleave', this.handlePoolDragLeave.bind(this))
        this.pool.addEventListener('drop', this.handleDropBackToPool.bind(this))

        // Tooltips
        document.querySelectorAll('.emission-block.in-grid').forEach(block => {
            this.setupTooltipListeners(block)
        })
    }

    makeDraggable(el) {
        el.setAttribute('draggable', true)
        el.dataset.source = 'pool'
        el.addEventListener('dragstart', (e) => {
            this.draggedElement = el
            this.sourceDropzone = el.closest('.dropzone') || null
        })
        this.setupTooltipListeners(el)
    }

    setupTooltipListeners(el) {
    el.addEventListener('mouseover', (e) => {
        const tooltipText = e.currentTarget.dataset.tooltip || ''
        this.tooltip.innerText = tooltipText
        this.tooltip.style.display = 'block'
    })

    el.addEventListener('mousemove', (e) => {
        this.tooltip.style.top = `${e.clientY + 12}px`
        this.tooltip.style.left = `${e.clientX + 12}px`
    })

    el.addEventListener('mouseout', () => {
        this.tooltip.style.display = 'none'
    })
}


    handleDragOver(e) {
        e.preventDefault()
        e.currentTarget.classList.add('drag-over')
    }

    handleDragLeave(e) {
        e.currentTarget.classList.remove('drag-over')
    }

    handlePoolDragOver(e) {
        e.preventDefault()
        this.pool.classList.add('drop-pool-hover')
    }

    handlePoolDragLeave(e) {
        this.pool.classList.remove('drop-pool-hover')
    }

    formatLocalDate(date) {
        const yyyy = date.getFullYear()
        const mm = String(date.getMonth() + 1).padStart(2, '0')
        const dd = String(date.getDate()).padStart(2, '0')
        const hh = String(date.getHours()).padStart(2, '0')
        const min = String(date.getMinutes()).padStart(2, '0')
        return `${yyyy}-${mm}-${dd} ${hh}:${min}:00`
    }

    handleDrop(e) {
        e.preventDefault()
        e.currentTarget.classList.remove('drag-over')

        const dropzone = e.currentTarget
        const allDropzones = this.dropzoneTargets
        const startDate = new Date(dropzone.dataset.date)
        const duration = parseInt(this.draggedElement.dataset.duration) || 15
        const heightPer15Min = 8
        const calculatedHeight = (duration / 15) * heightPer15Min
        const cellsToCover = Math.ceil(duration / 15)

        // Vérifie occupation
        for (let i = 0; i < cellsToCover; i++) {
            const futureDate = new Date(startDate.getTime() + i * 15 * 60000)
            const dz = allDropzones.find(el => el.dataset.date === this.formatLocalDate(futureDate))
            if (!dz || dz.classList.contains('occupied')) {
                console.warn('Cellule occupée')
                return
            }
        }

        // Si déjà dans la grille, libérer les anciennes cellules
        if (this.sourceDropzone) {
            const oldStart = new Date(this.sourceDropzone.dataset.date)
            const oldDuration = parseInt(this.draggedElement.dataset.duration)
            const oldCells = Math.ceil(oldDuration / 15)
            for (let i = 0; i < oldCells; i++) {
                const d = new Date(oldStart.getTime() + i * 15 * 60000)
                const dz = allDropzones.find(el => el.dataset.date === this.formatLocalDate(d))
                if (dz) {
                    dz.classList.remove('occupied')
                    dz.innerHTML = ''
                }
            }
        } else {
            // Si venait du pool, on l'enlève
            this.draggedElement.remove()
        }

        // Mise en forme
        const bloc = this.draggedElement
        bloc.classList.add('in-grid')
        bloc.dataset.source = 'grid'
        bloc.style.height = `${calculatedHeight}px`

        dropzone.innerHTML = ''
        dropzone.appendChild(bloc)

        for (let i = 0; i < cellsToCover; i++) {
            const futureDate = new Date(startDate.getTime() + i * 15 * 60000)
            const dz = allDropzones.find(el => el.dataset.date === this.formatLocalDate(futureDate))
            if (dz) dz.classList.add('occupied')
        }
    }

    handleDropBackToPool(e) {
        e.preventDefault()
        this.pool.classList.remove('drop-pool-hover')

        const el = this.draggedElement
        if (!el || el.dataset.source !== 'grid') return

        const allDropzones = this.dropzoneTargets
        const duration = parseInt(el.dataset.duration) || 15
        const startDate = new Date(this.sourceDropzone.dataset.date)
        const cellsToFree = Math.ceil(duration / 15)

        for (let i = 0; i < cellsToFree; i++) {
            const futureDate = new Date(startDate.getTime() + i * 15 * 60000)
            const dz = allDropzones.find(el => el.dataset.date === this.formatLocalDate(futureDate))
            if (dz) {
                dz.classList.remove('occupied')
                dz.innerHTML = ''
            }
        }

        // Retour dans la liste
        el.classList.remove('in-grid')
        el.dataset.source = 'pool'
        el.style = ''
        this.pool.appendChild(el)
    }
}
