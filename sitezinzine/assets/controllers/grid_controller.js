import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['day', 'emptyState', 'sidebarPanel', 'slotSummary', 'emissionsList']

  connect() {
    this.CELL_MIN = 15
    this.CELL_H   = 8

    this.dragged = null
    this.fromDay = null
    this.fromStartIndex = null
    this.selectedPostit = null

    this.element.querySelectorAll('.postit').forEach(el => this.makeDraggable(el, 'grid'))

    this.dayTargets.forEach(day => {
      day.addEventListener('dragover', e => { e.preventDefault(); day.classList.add('drag-over') })
      day.addEventListener('dragleave', () => day.classList.remove('drag-over'))
      day.addEventListener('drop', e => this.dropOnDay(e, day))
    })

    const pool = this.element.querySelector('#emissions-pool')
    if (pool) {
      pool.addEventListener('dragover', e => { e.preventDefault(); pool.classList.add('drop-pool-hover') })
      pool.addEventListener('dragleave', () => pool.classList.remove('drop-pool-hover'))
      pool.addEventListener('drop', e => this.dropBackToPool(e, pool))
    }
  }

  durationToCells(d) {
    d = parseInt(d || '15', 10)
    return Math.max(1, Math.ceil(d / this.CELL_MIN))
  }

  durationToPx(d) {
    d = parseInt(d || '15', 10)
    return (d / this.CELL_MIN) * this.CELL_H
  }

  makeDraggable(el, source) {
    el.setAttribute('draggable', 'true')
    el.dataset.source = source

    el.addEventListener('dragstart', () => {
      this.dragged = el

      if (source === 'grid') {
        this.fromDay = el.closest('.day-col')
        const top = parseFloat(el.style.top || '0')
        this.fromStartIndex = Math.round(top / this.CELL_H)
      } else {
        this.fromDay = null
        this.fromStartIndex = null
      }
    })
  }

  placePostIt(dayEl, startIndex) {
    const duration = parseInt(this.dragged.dataset.duration || '15', 10)
    const heightPx = this.durationToPx(duration)
    const cells = this.durationToCells(duration)

    if (startIndex + cells > 96) startIndex = 96 - cells
    if (startIndex < 0) startIndex = 0

    if (this.dragged.dataset.source === 'pool') {
      dayEl.appendChild(this.dragged)
      this.dragged.dataset.source = 'grid'
    }

    this.dragged.classList.add('postit')
    this.dragged.style.top = `${startIndex * this.CELL_H}px`
    this.dragged.style.left = '4px'
    this.dragged.style.right = '4px'
    this.dragged.style.height = `${heightPx}px`

    this.fromDay = dayEl
    this.fromStartIndex = startIndex
  }

  dropOnDay(e, dayEl) {
    dayEl.classList.remove('drag-over')
    if (!this.dragged) return

    const rect = dayEl.getBoundingClientRect()
    let startIndex = Math.floor((e.clientY - rect.top) / this.CELL_H)

    this.placePostIt(dayEl, startIndex)
  }

  dropBackToPool(e, pool) {
    e.preventDefault()
    pool.classList.remove('drop-pool-hover')
    if (!this.dragged || this.dragged.dataset.source !== 'grid') return

    this.dragged.removeAttribute('style')
    this.dragged.classList.remove('postit')
    this.dragged.dataset.source = 'pool'
    pool.appendChild(this.dragged)

    this.dragged = null
    this.fromDay = null
    this.fromStartIndex = null
  }

  async selectSlot(event) {
    const postit = event.currentTarget

    if (this.selectedPostit) {
      this.selectedPostit.classList.remove('is-selected')
    }

    this.selectedPostit = postit
    this.selectedPostit.classList.add('is-selected')

    const categoryTitle = postit.dataset.categoryTitle || 'Catégorie inconnue'
    const duration = postit.dataset.duration || ''
    const startsAt = postit.dataset.startsAt || ''
    const broadcastRank = postit.dataset.broadcastRank || ''
    const ruleId = postit.dataset.ruleId || ''
    const slotId = postit.dataset.slotId || ''

    this.emptyStateTarget.style.display = 'none'
    this.sidebarPanelTarget.style.display = 'block'

    this.slotSummaryTarget.innerHTML = `
      <div><span class="label">Catégorie :</span> ${categoryTitle}</div>
      <div><span class="label">Début :</span> ${startsAt}</div>
      <div><span class="label">Durée :</span> ${duration} min</div>
      <div><span class="label">Rang :</span> ${broadcastRank}</div>
    `

    this.emissionsListTarget.innerHTML = '<div>Chargement…</div>'

    try {
      const url = `/admin/grille/candidates?ruleId=${encodeURIComponent(ruleId)}&slotId=${encodeURIComponent(slotId)}&startsAt=${encodeURIComponent(startsAt)}`
      const response = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })

      if (!response.ok) {
        throw new Error('Réponse invalide')
      }

      const data = await response.json()
      this.renderEmissions(data)
    } catch (error) {
      this.emissionsListTarget.innerHTML = '<div>Impossible de charger les émissions.</div>'
    }
  }

  renderEmissions(data) {
    const items = Array.isArray(data.items) ? data.items : []

    if (items.length === 0) {
      this.emissionsListTarget.innerHTML = '<div>Aucune émission compatible pour le moment.</div>'
      return
    }

    this.emissionsListTarget.innerHTML = items.map(item => `
      <div class="emission-card" data-emission-id="${item.id}">
        ${item.title}
        <small>${item.meta ?? ''}</small>
      </div>
    `).join('')
  }
}