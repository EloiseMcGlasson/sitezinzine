import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['day', 'emptyState', 'sidebarPanel', 'slotSummary', 'emissionsList', 'slotActions']

  connect() {
    this.CELL_MIN = 15
    this.CELL_H = 8

    this.dragged = null
    this.fromDay = null
    this.fromStartIndex = null
    this.selectedPostit = null

    this.element.querySelectorAll('.postit').forEach(el => this.makeDraggable(el, 'grid'))

    this.dayTargets.forEach(day => {
      day.addEventListener('dragover', e => {
        e.preventDefault()
        day.classList.add('drag-over')
      })

      day.addEventListener('dragleave', () => {
        day.classList.remove('drag-over')
      })

      day.addEventListener('drop', e => this.dropOnDay(e, day))
    })

    const pool = this.element.querySelector('#emissions-pool')
    if (pool) {
      pool.addEventListener('dragover', e => {
        e.preventDefault()
        pool.classList.add('drop-pool-hover')
      })

      pool.addEventListener('dragleave', () => {
        pool.classList.remove('drop-pool-hover')
      })

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
    const assignedEmissionTitle = postit.dataset.assignedEmissionTitle || ''

    this.emptyStateTarget.style.display = 'none'
    this.sidebarPanelTarget.style.display = 'block'

    this.slotSummaryTarget.innerHTML = `
    <div><span class="label">Catégorie :</span> ${categoryTitle}</div>
    <div><span class="label">Début :</span> ${startsAt}</div>
    <div><span class="label">Durée :</span> ${duration} min</div>
    <div><span class="label">Rang :</span> ${broadcastRank}</div>
    ${assignedEmissionTitle
        ? `<div><span class="label">Émission affectée :</span> ${assignedEmissionTitle}</div>`
        : ''
      }
  `

    this.slotActionsTarget.style.display = assignedEmissionTitle ? 'block' : 'none'

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
      <div
        class="emission-card"
        data-emission-id="${item.id}"
        data-action="click->grid#selectEmission"
      >
        ${item.title}
        <small>${item.meta ?? ''}</small>
      </div>
    `).join('')
  }

  async selectEmission(event) {
  const card = event.currentTarget
  const emissionId = card.dataset.emissionId

  if (!this.selectedPostit) {
    return
  }

  const slotId = this.selectedPostit.dataset.slotId || ''
  const startsAt = this.selectedPostit.dataset.startsAt || ''
  const duration = this.selectedPostit.dataset.duration || ''
  const categoryTitle = this.selectedPostit.dataset.categoryTitle || 'Catégorie inconnue'
  const broadcastRank = this.selectedPostit.dataset.broadcastRank || ''

  if (!slotId || !startsAt || !emissionId) {
    alert('Informations incomplètes pour affecter cette émission.')
    return
  }

  try {
    const response = await fetch('/admin/grille/assign', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: new URLSearchParams({
        slotId,
        emissionId,
        startsAt
      })
    })

    if (!response.ok) {
      throw new Error('Réponse invalide')
    }

    const data = await response.json()

    if (!data.success) {
      throw new Error(data.error || 'Erreur inconnue')
    }

    if (data.propagated === true) {
      window.location.reload()
      return
    }

    this.selectedPostit.innerHTML = `🎙 ${data.emissionTitle} • ${duration} min`
    this.selectedPostit.dataset.assignedEmissionId = emissionId
    this.selectedPostit.dataset.assignedEmissionTitle = data.emissionTitle
    this.selectedPostit.classList.add('assigned')

    this.slotSummaryTarget.innerHTML = `
      <div><span class="label">Catégorie :</span> ${categoryTitle}</div>
      <div><span class="label">Début :</span> ${startsAt}</div>
      <div><span class="label">Durée :</span> ${duration} min</div>
      <div><span class="label">Rang :</span> ${broadcastRank}</div>
      <div><span class="label">Émission affectée :</span> ${data.emissionTitle}</div>
    `

    this.slotActionsTarget.style.display = 'block'

    const cards = this.emissionsListTarget.querySelectorAll('.emission-card')
    cards.forEach(el => el.classList.remove('is-selected'))
    card.classList.add('is-selected')

  } catch (error) {
    alert('Erreur lors de l’affectation de l’émission.')
  }
}

  async removeAssignment() {
    if (!this.selectedPostit) {
      return
    }

    const slotId = this.selectedPostit.dataset.slotId || ''
    const startsAt = this.selectedPostit.dataset.startsAt || ''
    const duration = this.selectedPostit.dataset.duration || ''
    const categoryTitle = this.selectedPostit.dataset.categoryTitle || 'Catégorie inconnue'
    const broadcastRank = this.selectedPostit.dataset.broadcastRank || ''

    if (!slotId || !startsAt) {
      alert('Informations incomplètes pour retirer cette émission.')
      return
    }

    try {
      const response = await fetch('/admin/grille/remove', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          slotId,
          startsAt
        })
      })

      if (!response.ok) {
        throw new Error('Réponse invalide')
      }

      const data = await response.json()

      if (!data.success) {
        throw new Error(data.error || 'Erreur inconnue')
      }

      this.selectedPostit.innerHTML = `🎙 ${categoryTitle} • ${duration} min`
      this.selectedPostit.dataset.assignedEmissionId = ''
      this.selectedPostit.dataset.assignedEmissionTitle = ''
      this.selectedPostit.classList.remove('assigned')

      this.slotSummaryTarget.innerHTML = `
      <div><span class="label">Catégorie :</span> ${categoryTitle}</div>
      <div><span class="label">Début :</span> ${startsAt}</div>
      <div><span class="label">Durée :</span> ${duration} min</div>
      <div><span class="label">Rang :</span> ${broadcastRank}</div>
    `

      this.slotActionsTarget.style.display = 'none'

    } catch (error) {
      alert('Erreur lors de la suppression de l’émission.')
    }
  }
}