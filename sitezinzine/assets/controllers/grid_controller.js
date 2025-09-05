import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['day','emission']  // plus besoin de 'slot'

  connect() {
    // 1 slot = 15 min ; 1 slot = 8px
    this.CELL_MIN = 15
    this.CELL_H   = 8

    this.dragged = null
    this.fromDay = null
    this.fromStartIndex = null

    // rendre draggables : post-its déjà en grille (et plus tard ceux du pool)
    this.element.querySelectorAll('.postit').forEach(el => this.makeDraggable(el, 'grid'))

    // colonnes “jour” (drop global)
    this.dayTargets.forEach(day => {
      day.addEventListener('dragover', e => { e.preventDefault(); day.classList.add('drag-over') })
      day.addEventListener('dragleave', () => day.classList.remove('drag-over'))
      day.addEventListener('drop', e => this.dropOnDay(e, day))
    })

    // pool (optionnel, prêt pour plus tard)
    const pool = this.element.querySelector('#emissions-pool')
    if (pool) {
      pool.addEventListener('dragover', e => { e.preventDefault(); pool.classList.add('drop-pool-hover') })
      pool.addEventListener('dragleave', () => pool.classList.remove('drop-pool-hover'))
      pool.addEventListener('drop', e => this.dropBackToPool(e, pool))
    }
  }

  // helpers durée
  durationToCells(d){ d=parseInt(d||'15',10); return Math.max(1, Math.ceil(d/this.CELL_MIN)) }
  durationToPx(d){ d=parseInt(d||'15',10); return (d/this.CELL_MIN)*this.CELL_H }

  makeDraggable(el, source) {
    el.setAttribute('draggable','true')
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

  // place/replace un post-it dans une colonne jour
  placePostIt(dayEl, startIndex) {
    const duration = parseInt(this.dragged.dataset.duration || '15', 10)
    const heightPx = this.durationToPx(duration)
    const cells    = this.durationToCells(duration)

    // clamp en bas de journée
    if (startIndex + cells > 96) startIndex = 96 - cells
    if (startIndex < 0) startIndex = 0

    // si vient du pool, rattacher à la colonne
    if (this.dragged.dataset.source === 'pool') {
      dayEl.appendChild(this.dragged)
      this.dragged.dataset.source = 'grid'
    }

    // style/position
    this.dragged.classList.add('postit')
    this.dragged.style.top    = `${startIndex * this.CELL_H}px`
    this.dragged.style.left   = '4px'
    this.dragged.style.right  = '4px'
    this.dragged.style.height = `${heightPx}px`

    // mémoriser
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

  // retour au pool (quand tu activeras la colonne droite)
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
}
