/**
 * Portfolio Géraldo Perridys AGONSE - Frontend
 * Tailwind est compile via Vite, Livewire (avec Alpine.js) gere l'interactivite.
 */

document.addEventListener('alpine:init', () => {
    // Visionneuse de la galerie : affichage en grand format (CC §13).
    // `items` est un tableau d'objets { url, caption }.
    Alpine.data('galleryViewer', (items) => ({
        items: items || [],
        current: null,

        get hasItems() {
            return this.items.length > 0
        },

        open(index) {
            if (!this.hasItems) return
            this.current = this.items[index] || null
            document.body.style.overflow = 'hidden'
        },

        close() {
            this.current = null
            document.body.style.overflow = ''
        },

        index() {
            return this.items.findIndex((item) => item.url === (this.current && this.current.url))
        },

        next() {
            if (!this.hasItems) return
            this.current = this.items[(this.index() + 1) % this.items.length]
        },

        previous() {
            if (!this.hasItems) return
            const i = this.index()
            this.current = this.items[(i - 1 + this.items.length) % this.items.length]
        },

        // Navigation au clavier quand la visionneuse est ouverte
        onKey(event) {
            if (!this.current) return
            if (event.key === 'Escape') this.close()
            if (event.key === 'ArrowRight') this.next()
            if (event.key === 'ArrowLeft') this.previous()
        },
    }))

    // Filtre de la galerie par catégorie éventuelle
    Alpine.data('galleryFilter', (categories) => ({
        categories: categories || [],
        active: 'all',
        isVisible(category) {
            return this.active === 'all' || this.active === category
        },
    }))
})
