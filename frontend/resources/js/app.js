/**
 * Portfolio Géraldo Perridys AGONSE - Frontend
 * Tailwind est compile via Vite, Livewire (avec Alpine.js) gere l'interactivite.
 */

// --- Champ de mot de passe : oeil afficher / masquer ------------------------
// Volontairement en JavaScript natif et non en Alpine : la page de connexion
// ne charge pas Livewire, or elle a aussi besoin de l'oeil. La delegation
// d'evenement couvre les champs presents et ceux ajoutes plus tard.
document.addEventListener('click', (event) => {
    const bouton = event.target.closest('[data-password-toggle]')

    if (!bouton) return

    const conteneur = bouton.closest('[data-password]') || bouton.parentElement
    const champ = conteneur ? conteneur.querySelector('input') : null

    if (!champ) return

    // Le champ est-il actuellement en clair ?
    const visible = champ.type === 'text'

    champ.type = visible ? 'password' : 'text'

    bouton.setAttribute('aria-pressed', visible ? 'false' : 'true')
    bouton.setAttribute('aria-label', visible ? 'Afficher le mot de passe' : 'Masquer le mot de passe')
    bouton.setAttribute('title', visible ? 'Afficher le mot de passe' : 'Masquer le mot de passe')

    // Apres le clic, le texte est visible si et seulement s'il etait masque.
    const texteVisible = !visible

    // L'icone propose toujours l'action inverse : oeil ouvert quand le texte
    // est masque (cliquer pour le montrer), oeil barre quand il est montre
    // (cliquer pour le cacher).
    const iconeAttendue = texteVisible ? 'hidden' : 'visible'

    bouton.querySelectorAll('[data-password-icon]').forEach((icone) => {
        icone.classList.toggle('hidden', icone.dataset.passwordIcon !== iconeAttendue)
    })

    // Conserve la position du curseur : basculer type reaffecte le champ.
    const position = champ.selectionStart
    champ.focus()

    if (position !== null) {
        try {
            champ.setSelectionRange(position, position)
        } catch (error) {
            // Certains navigateurs refusent setSelectionRange sur un input
            // dont le type vient de changer : sans consequence, on ignore.
        }
    }
})

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
