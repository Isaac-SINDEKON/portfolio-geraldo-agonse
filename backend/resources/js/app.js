/**
 * Portfolio Géraldo Perridys AGONSE - Frontend
 *
 * Tailwind est compile via Vite, Livewire (avec Alpine.js) gere l'interactivite.
 *
 * Ce fichier ajoute le moteur d'animation de la maquette. Il repose sur des
 * attributs declaratifs poses dans les vues, ce qui evite de dupliquer de la
 * logique Alpine a chaque bloc :
 *
 *   data-reveal="up|down|left|right|zoom|blur|flip|none"  apparition au defilement
 *   data-reveal-group                                      declenche en cascade
 *   data-count="120"                                       compteur anime
 *   data-tilt                                              inclinaison 3D
 *   data-glow                                              halo suivant le pointeur
 *   data-parallax="0.12"                                   derive au defilement
 *   data-flip                                              icones de WhatsApp
 *
 * Tout est neutralise si le visiteur demande explicitement moins d'animation,
 * et les elements restent visibles si JavaScript est indisponible.
 */

// Regle unique du site : moins d'animation, on n'anime rien. La valeur est
// réévaluée à chaque initialisation : un visiteur peut changer la préférence
// de son système sans recharger la page.
const mouvementReduit = window.matchMedia('(prefers-reduced-motion: reduce)')

let anime = ! mouvementReduit.matches

const observateurReveal = anime && 'IntersectionObserver' in window
    ? new IntersectionObserver((entrees, observateur) => {
        entrees.forEach((entree) => {
            if (! entree.isIntersecting) {
                return
            }

            entree.target.classList.add('est-visible')
            observateur.unobserve(entree.target)
        })
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 })
    : null

/**
 * Rend visibles immediatement les elements deja a l'ecran (au-dessus de la
 * ligne de flottaison). Sans cela, un bloc visible au chargement resterait
 * masque jusqu'au premier evenement de defilement.
 */
function revelerCeQuiEstVisible() {
    document.querySelectorAll('[data-reveal]').forEach((element) => {
        if (element.classList.contains('est-visible')) {
            return
        }

        const rect = element.getBoundingClientRect()

        if (rect.top < window.innerHeight * 0.92 && rect.bottom > 0) {
            element.classList.add('est-visible')
        }
    })
}

/** Cascade : decale chaque enfant pour une apparition en vague. */
function preparerGroupes() {
    document.querySelectorAll('[data-reveal-group]').forEach((groupe) => {
        const pas = Number(groupe.dataset.revealStep || 90)

        Array.from(groupe.children).forEach((enfant, index) => {
            if (enfant.hasAttribute('data-reveal')) {
                return
            }

            enfant.setAttribute('data-reveal', 'up')

            if (! enfant.style.getPropertyValue('--reveal-delay')) {
                enfant.style.setProperty('--reveal-delay', index * pas + 'ms')
            }
        })
    })
}

function brancherReveal() {
    document.querySelectorAll('[data-reveal]').forEach((element) => {
        if (! observateurReveal) {
            // Sans IntersectionObserver (ou mouvement réduit), on affiche tout.
            element.classList.add('est-visible')
            return
        }

        observateurReveal.observe(element)
    })

    revelerCeQuiEstVisible()
}

/**
 * Compteur anime. La valeur cible reste lisible sans JavaScript : le
 * parametre `data-count` porte le nombre, le script ne fait que l'ecrire.
 * Les separateurs de milliers du texte final sont preserves.
 */
function animerCompteur(element) {
    const cible = Number(element.dataset.count)

    if (! Number.isFinite(cible)) {
        return
    }

    if (! anime) {
        element.textContent = element.dataset.countFinal || element.textContent
        return
    }

    const duree = 1500
    const depart = performance.now()
    const formatFinal = element.dataset.countFinal || element.textContent

    function pas(maintenant) {
        const avance = Math.min((maintenant - depart) / duree, 1)

        // easeOutExpo : l'acceleration est rapide, l'arrivee amortie.
        const facteur = avance === 1 ? 1 : 1 - Math.pow(2, -10 * avance)

        element.textContent = formatFinal.replace(
            /\d+/,
            String(Math.round(cible * facteur))
        )

        if (avance < 1) {
            requestAnimationFrame(pas)
        }
    }

    requestAnimationFrame(pas)
}

function brancherCompteurs() {
    const compteurs = document.querySelectorAll('[data-count]')

    if (! compteurs.length) {
        return
    }

    if (! anime || ! ('IntersectionObserver' in window)) {
        return
    }

    const observateur = new IntersectionObserver((entrees, obs) => {
        entrees.forEach((entree) => {
            if (! entree.isIntersecting) {
                return
            }

            animerCompteur(entree.target)
            obs.unobserve(entree.target)
        })
    }, { threshold: 0.4 })

    compteurs.forEach((compteur) => observateur.observe(compteur))
}

/**
 * Livewire rappelle le moteur après chaque rendu. Sans ce garde-fou, les
 * écouteurs `pointermove` seraient empilés sur le même élément : à la
 * vingtième interaction, le halo et le bouton magnétique répondraient vingt
 * fois au même mouvement. On marque donc l'élément comme déjà branché.
 */
function brancherUneFois(selecteur, branche) {
    document.querySelectorAll(selecteur).forEach((element) => {
        if (element.dataset.branche) {
            return
        }

        element.dataset.branche = '1'

        branche(element)
    })
}

/** Inclinaison 3D douce, active uniquement sur les appareils a pointeur fin. */
function brancherTilt() {
    if (! anime || ! window.matchMedia('(hover: hover)').matches) {
        return
    }

    brancherUneFois('[data-tilt]', (element) => {
        const max = Number(element.dataset.tiltMax || 6)

        element.addEventListener('pointermove', (evenement) => {
            const rect = element.getBoundingClientRect()

            const x = (evenement.clientX - rect.left) / rect.width - 0.5
            const y = (evenement.clientY - rect.top) / rect.height - 0.5

            element.classList.add('est-incline')
            element.style.setProperty('--tilt-x', (-y * max).toFixed(2) + 'deg')
            element.style.setProperty('--tilt-y', (x * max).toFixed(2) + 'deg')
        })

        element.addEventListener('pointerleave', () => {
            element.classList.remove('est-incline')
            element.style.setProperty('--tilt-x', '0deg')
            element.style.setProperty('--tilt-y', '0deg')
        })
    })
}

/** Halo de couleur qui suit le pointeur sur les cartes. */
function brancherGlow() {
    if (! window.matchMedia('(hover: hover)').matches) {
        return
    }

    brancherUneFois('[data-glow]', (element) => {
        element.addEventListener('pointerenter', () => element.classList.add('est-survol'))

        element.addEventListener('pointermove', (evenement) => {
            const rect = element.getBoundingClientRect()

            element.style.setProperty('--glow-x', (evenement.clientX - rect.left) + 'px')
            element.style.setProperty('--glow-y', (evenement.clientY - rect.top) + 'px')
        })

        element.addEventListener('pointerleave', () => element.classList.remove('est-survol'))
    })
}

/**
 * Bouton magnétique : l'élément suit le pointeur avec un retard élastique et
 * revient à sa place au départ. Seules deux variables CSS sont écrites, le
 * mouvement est donc piloté par app.css et reste désactivable d'un coup.
 */
function brancherMagnetiques() {
    if (! anime || ! window.matchMedia('(hover: hover)').matches) {
        return
    }

    brancherUneFois('[data-magnet]', (element) => {
        // 0.25 : déplacement max d'un quart de l'écart au pointeur. Au-delà,
        // le bouton semble quitter son emplacement et le clic devient hasardeux.
        const force = Number(element.dataset.magnet) || 0.25

        element.addEventListener('pointermove', (evenement) => {
            const rect = element.getBoundingClientRect()

            const dx = evenement.clientX - (rect.left + rect.width / 2)
            const dy = evenement.clientY - (rect.top + rect.height / 2)

            element.classList.add('est-attire')
            element.style.setProperty('--magnet-x', (dx * force).toFixed(1) + 'px')
            element.style.setProperty('--magnet-y', (dy * force).toFixed(1) + 'px')
            element.style.setProperty('--glow-x', (evenement.clientX - rect.left) + 'px')
            element.style.setProperty('--glow-y', (evenement.clientY - rect.top) + 'px')
        })

        element.addEventListener('pointerenter', () => element.classList.add('est-survol'))

        element.addEventListener('pointerleave', () => {
            element.classList.remove('est-attire', 'est-survol')
            element.style.setProperty('--magnet-x', '0px')
            element.style.setProperty('--magnet-y', '0px')
        })
    })
}

/** Derive lente des decors au defilement (une seule boucle rAF partagee). */
function brancherParallaxe() {
    const elements = Array.from(document.querySelectorAll('[data-parallax]'))
        .map((element) => ({ element, force: Number(element.dataset.parallax) || 0.1 }))

    if (! elements.length || ! anime) {
        return
    }

    let enAttente = false

    function peindre() {
        enAttente = false

        const centre = window.innerHeight / 2

        elements.forEach(({ element, force }) => {
            const rect = element.getBoundingClientRect()

            // Invisible : inutile de calculer une position qu'on ne verra pas.
            if (rect.bottom < -200 || rect.top > window.innerHeight + 200) {
                return
            }

            const decalage = (rect.top + rect.height / 2 - centre) * force

            element.style.transform = 'translate3d(0,' + decalage.toFixed(1) + 'px,0)'
        })
    }

    function demander() {
        if (enAttente) {
            return
        }

        enAttente = true
        requestAnimationFrame(peindre)
    }

    window.addEventListener('scroll', demander, { passive: true })
    window.addEventListener('resize', demander)

    peindre()
}

/**
 * Barre de progression de lecture. Une seule variable CSS est mise a jour,
 * le CSS fait le reste : aucune mesure de largeur par ecriture de style.
 */
function brancherProgression() {
    const barre = document.querySelector('[data-barre-progression]')

    if (! barre || ! anime) {
        return
    }

    let enAttente = false

    function peindre() {
        enAttente = false

        const hauteur = document.documentElement.scrollHeight - window.innerHeight

        const ratio = hauteur > 0 ? window.scrollY / hauteur : 0

        barre.style.setProperty('--scroll', Math.max(0, Math.min(1, ratio)).toFixed(4))
    }

    function demander() {
        if (enAttente) {
            return
        }

        enAttente = true
        requestAnimationFrame(peindre)
    }

    window.addEventListener('scroll', demander, { passive: true })
    window.addEventListener('resize', demander)

    peindre()
}

/**
 * Le salut de l'icône WhatsApp, à intervalles irréguliers et jamais sous le
 * curseur : animer un bouton que le visiteur est en train de viser revient à
 * déplacer la cible du clic. L'anneau respirant, lui, joue ce rôle en
 * permanence et sans jamais bouger la pastille.
 */
function brancherFrappe() {
    if (! anime) {
        return
    }

    brancherUneFois('[data-frappe]', (element) => {
        // Le sélecteur vise l'icône, pas le lien : c'est elle qui salue.
        const icone = element.querySelector('.frappe-icone') || element

        function saluer() {
            if (element.matches(':hover, :focus-visible')) {
                return
            }

            icone.classList.remove('est-frappe')

            // Relance l'animation : la classe doit repartir de zéro.
            void icone.offsetWidth

            icone.classList.add('est-frappe')
        }

        // 5,2 s puis 6,9 s puis 8,4 s, puis boucle. Irrégulier, donc jamais
        // perçu comme une animation de chargement.
        const intervalles = [5200, 6900, 8400]
        let tour = 0

        function programmer() {
            setTimeout(() => {
                saluer()

                tour = (tour + 1) % intervalles.length

                programmer()
            }, intervalles[tour])
        }

        programmer()
    })
}

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

// --- Thème clair / sombre ----------------------------------------------------
// Le bascule est applique sur <html> et memorize dans localStorage. Le
// script anti-clignotement du <head> a deja pose l'etat au premier rendu :
// ici on ne fait que brancher le bouton et reagir aux changements externes.
const CLE_THEME = 'theme-portfolio'
const TOUS_THEMES = ['clair', 'sombre']

function nommerTheme(theme) {
    return TOUS_THEMES.includes(theme) ? theme : null
}

function appliquerTheme(theme) {
    const sombre = theme === 'sombre'

    document.documentElement.classList.toggle('dark', sombre)
    document.documentElement.style.colorScheme = sombre ? 'dark' : 'light'

    // Toujours 'clair' ou 'sombre' : le script du <head> et l'icone du bouton
    // lisent cette valeur pour savoir dans quel thème on se trouve, sans avoir
    // à refaire le calcul.
    document.documentElement.dataset.theme = sombre ? 'sombre' : 'clair'
}

document.addEventListener('theme:changer', (evenement) => {
    const theme = nommerTheme(evenement.detail)

    if (! theme) return

    appliquerTheme(theme)

    try {
        localStorage.setItem(CLE_THEME, theme)
    } catch (erreur) {
        // Navigation privee : le theme vaut pour la session seulement.
    }
})

document.addEventListener('alpine:init', () => {
    // Bascule clair / sombre. L'icone annonce toujours l'action proposee et
    // non l'etat courant : on clique pour passer dans l'autre theme.
    Alpine.data('basculeTheme', () => ({
        theme: 'clair',

        init() {
            // L'etat effectif est deja pose par le script du <head> : on le lit
            // sur <html> plutot que de le deviner, ce qui garantit que l'icone
            // et l'affichage repondent a la meme source.
            this.theme = document.documentElement.classList.contains('dark')
                ? 'sombre'
                : 'clair'

            this.$watch('theme', (valeur) => {
                document.dispatchEvent(
                    new CustomEvent('theme:changer', { detail: valeur })
                )
            })
        },

        get sombre() {
            return this.theme === 'sombre'
        },

        get libelle() {
            return this.sombre ? 'Passer en thème clair' : 'Passer en thème sombre'
        },

        basculer() {
            this.theme = this.sombre ? 'clair' : 'sombre'
        },
    }))

    // En-tête : barre qui se contracte au défilement + panneau modal qui
    // bloque le défilement de la page tant qu'il est ouvert. Une seule boucle
    // rAF garde le calcul propre même si le défilement est tres rapide.
    Alpine.data('headerBar', () => ({
        open: false,
        compact: false,
        enAttente: false,
        declencheur: null,
        panneau: null,

        init() {
            this.mesurer()

            this.$watch('open', (ouvert) => {
                this.verrouiller(ouvert)
            })
        },

        mesurer() {
            if (this.enAttente) {
                return
            }

            this.enAttente = true

            requestAnimationFrame(() => {
                this.compact = window.scrollY > 24
                this.enAttente = false
            })
        },

        basculer() {
            this.open = ! this.open
        },

        fermer() {
            this.open = false
        },

        /**
         * Bloque le défilement de la page. L'ascenseur du navigateur disparaît
         * alors, ce qui élargit le document de sa largeur et fait sauter tout le
         * texte vers la gauche pendant une frame. On rend cette largeur en
         * marge droite, sinon le site « respire » à chaque ouverture.
         */
        verrouiller(ouvert) {
            if (! ouvert) {
                document.body.style.overflow = ''
                document.body.style.paddingRight = ''

                // Le focus revient au bouton qui a ouvert le panneau, sinon il
                // tombe sur `body` et la tabulation repart du haut de la page.
                if (this.declencheur) {
                    this.declencheur.focus({ preventScroll: true })
                }

                return
            }

            const largeurAscenseur = window.innerWidth - document.documentElement.clientWidth

            document.body.style.overflow = 'hidden'

            if (largeurAscenseur > 0) {
                document.body.style.paddingRight = largeurAscenseur + 'px'
            }

            // Le focus va au panneau lui-même, pas à un lien : la tabulation
            // entre alors dans le menu par le haut, et le coup de painted
            // remains hors de la cible jusqu'à la première tabulation.
            this.$nextTick(() => {
                if (this.panneau) {
                    this.panneau.focus({ preventScroll: true })
                }
            })
        },

        /**
         * Piege a focus. Un panneau modal doit garder le focus dedans, sinon la
         * tabulation finit par atteindre les liens situes derriere lui, que le
         * visiteur ne voit plus. Le plugin Focus n'etant pas installe, on
         * Ramene le focus sur le dernier element quand il sort par le bas.
         */
        piegerFocus(evenement) {
            if (evenement.key !== 'Tab') {
                return
            }

            const focalisables = Array.from(
                evenement.currentTarget.querySelectorAll(
                    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
                )
            ).filter((element) => element.offsetParent !== null)

            if (focalisables.length === 0) {
                return
            }

            const premier = focalisables[0]
            const dernier = focalisables[focalisables.length - 1]

            if (evenement.shiftKey && evenement.target === premier) {
                evenement.preventDefault()
                dernier.focus()
            } else if (! evenement.shiftKey && evenement.target === dernier) {
                evenement.preventDefault()
                premier.focus()
            }
        },
    }))

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

/**
 * Amorçage du moteur. Il attend le chargement du document pour ne pas perdre
 * les elements inseres par Livewire apres coup : les composants Livewire
 * appellent `reinitialiserAnimations()` apres chaque mise a jour.
 */
function initialiserAnimations() {
    // La préférence système peut avoir changé depuis le chargement.
    anime = ! mouvementReduit.matches

    preparerGroupes()
    brancherReveal()
    brancherCompteurs()
    brancherTilt()
    brancherGlow()
    brancherMagnetiques()
    brancherParallaxe()
    brancherProgression()
    brancherFrappe()
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialiserAnimations)
} else {
    initialiserAnimations()
}

// Livewire remplace une partie du DOM : les nouveaux blocs repartent de zero.
// Les ecouteurs globaux (delegation d'evenement) n'ont pas besoin d'etre
// reposes, seuls les observateurs element par element le sont.
document.addEventListener('livewire:navigated', initialiserAnimations)

window.reinitialiserAnimations = function () {
    initialiserAnimations()
}

// Un changement de preference systeme doit etre pris en compte immediatement.
mouvementReduit.addEventListener?.('change', initialiserAnimations)
// ---------------------------------------------------------------------------
// Copie dans le presse-papiers
// ---------------------------------------------------------------------------
// `navigator.clipboard` n'existe que dans un contexte securise (HTTPS, ou
// 127.0.0.1 en local). Sur un site servi en HTTP simple, il vaut `undefined` :
// une copie ecrite uniquement avec lui echoue silencieusement, et le visiteur
// croit que le bouton est casse. Le repli `document.execCommand('copy')` passe
// par un `textarea` hors ecran et fonctionne partout.
//
// Renvoie une promesse : `true` si le texte est bien dans le presse-papiers.

window.copierTexte = function copierTexte(texte) {
    const valeur = String(texte ?? '')

    if (! valeur) {
        return Promise.resolve(false)
    }

    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(valeur).then(() => true).catch(() => repliExecCommand(valeur))
    }

    return Promise.resolve(repliExecCommand(valeur))
}

function repliExecCommand(texte) {
    const champ = document.createElement('textarea')

    champ.value = texte
    // Hors ecran plutot que `display:none` : un element masque n'est pas
    // selectionnable, et la copie echouerait.
    champ.setAttribute('readonly', '')
    champ.style.cssText = 'position:fixed;top:0;left:0;width:1px;height:1px;padding:0;border:0;opacity:0'
    document.body.appendChild(champ)

    const selection = document.getSelection()
    const plagePrecedente = selection && selection.rangeCount > 0 ? selection.getRangeAt(0) : null

    champ.select()
    champ.setSelectionRange(0, texte.length)

    let copie = false

    try {
        copie = document.execCommand('copy')
    } catch (erreur) {
        copie = false
    }

    document.body.removeChild(champ)

    // On rend la selection precedente : un clic qui copie ne doit pas laisser
    // un texte bleu selectionne sur la page.
    if (plagePrecedente && selection) {
        selection.removeAllRanges()
        selection.addRange(plagePrecedente)
    }

    return copie
}

// ---------------------------------------------------------------------------
// Lien mail : fallback webmail si aucun client mail n'est configure
// ---------------------------------------------------------------------------
// Sur desktop sans client par defaut (Windows sans Outlook/Mail), le mailto:
// ne fait rien. Ce gestionnaire tente l'ouverture native, attend 800 ms, et
// si la page est encore visible/focussee, propose Gmail ET Outlook.com.
(function gererLiensMail() {
    const GMAIL_URL = 'https://mail.google.com/mail/?to={email}&su={subject}&body={body}';
    const OUTLOOK_URL = 'https://outlook.live.com/mail/0/deeplink/compose?to={email}&subject={subject}&body={body}';

    function ouvrirWebmail(modele, email, subject, body) {
        return modele
            .replace('{email}', encodeURIComponent(email))
            .replace('{subject}', encodeURIComponent(subject))
            .replace('{body}', encodeURIComponent(body));
    }

    document.addEventListener('click', function (evenement) {
        const lien = evenement.target.closest('a[href^="mailto:"]');

        if (!lien) {
            return;
        }

        const href = lien.getAttribute('href');
        const url = new URL(href, window.location.origin);

        // Tente l'ouverture native
        const ouvert = window.open(href, '_blank');

        // Si window.open renvoie null (bloqueur popup) ou si la page reste
        // active apres un delai, le client mail n'a pas pris la main.
        setTimeout(function () {
            const pageActive = !document.hidden && document.hasFocus();
            const pasDePopup = !ouvert || ouvert.closed || typeof ouvert.closed === 'undefined';

            if (pageActive && pasDePopup) {
                const email = url.pathname;
                const subject = url.searchParams.get('subject') || '';
                const body = url.searchParams.get('body') || '';

                const gmail = ouvrirWebmail(GMAIL_URL, email, subject, body);
                const outlook = ouvrirWebmail(OUTLOOK_URL, email, subject, body);

                // Affiche un toast avec les deux choix
                if (typeof window.montrer === 'function') {
                    window.montrer(
                        'Aucun client mail detecte. Choisissez : ' +
                        '<a href="' + gmail + '" target="_blank" rel="noopener noreferrer" class="underline text-primary-600 hover:text-primary-400 mr-3">Gmail</a>' +
                        '<a href="' + outlook + '" target="_blank" rel="noopener noreferrer" class="underline text-primary-600 hover:text-primary-400">Outlook.com</a>',
                        true
                    );
                } else {
                    // Fallback si le toast n'est pas dispo : ouvre Gmail par defaut
                    window.open(gmail, '_blank', 'noopener,noreferrer');
                }
            }
        }, 800);
    });
})();

// ---------------------------------------------------------------------------
// Etats de soumission
// ---------------------------------------------------------------------------
// L'administration est en Blade classique : une action POST recharge la page,
// donc aucun `wire:loading` n'est disponible la. On pose un verrou au moment ou
// le navigateur valide le formulaire : le bouton se desactive et affiche une
// animation. Cela supprime le double clic (le cas le plus grave etant deux
// DELETE d'affilee) et donne un retour immediat sur une action en attente.
//
// Le verrou est pose dans une tache differee : si un autre gestionnaire annule
// la soumission, `defaultPrevented` est vrai et on rend la main. Sans cela un
// formulaire refuse par le navigateur resterait desactive.

(function brancherEtatsDeSoumission() {
    const ANIMATION = '<svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path></svg>'

    document.addEventListener('submit', (evenement) => {
        const formulaire = evenement.target

        if (! (formulaire instanceof HTMLFormElement)) return

        // Livewire publie deja un retour de soumission sur ses propres
        // formulaires : on ne le double pas.
        if (formulaire.hasAttribute('wire:submit') || formulaire.closest('[wire\\:id]')) return

        setTimeout(() => {
            if (evenement.defaultPrevented) return

            formulaire.querySelectorAll('button[type="submit"], input[type="submit"]')
                .forEach((bouton) => {
                    if (bouton.disabled) return

                    bouton.dataset.libelleInitial = bouton.innerHTML
                    bouton.disabled = true
                    bouton.setAttribute('aria-busy', 'true')

                    if (bouton instanceof HTMLButtonElement) {
                        bouton.insertAdjacentHTML('afterbegin', ANIMATION)
                        bouton.classList.add('gap-2')
                    }
})
        }, 0)
    })
})()
