<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\Experience;
use App\Models\Formation;
use App\Models\GalleryImage;
use App\Models\Reason;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Le contenu de reference n'est insere que si la table concernee est vide.
     *
     * Sans cette garde, relancer le seeder ecraserait les textes, photos et
     * formations que le proprietaire a modifies depuis l'administration.
     */
    public function run(): void
    {
        $this->seedUser();
        $this->seedSettings();

        $this->seedIfEmpty(Domain::class, 'domaines', fn () => $this->seedDomains());
        $this->seedIfEmpty(Reason::class, 'raisons', fn () => $this->seedReasons());
        $this->seedIfEmpty(Formation::class, 'formations', fn () => $this->seedFormations());
        $this->seedIfEmpty(Service::class, 'services', fn () => $this->seedServices());
        $this->seedIfEmpty(Experience::class, 'experiences', fn () => $this->seedExperiences());
        $this->seedIfEmpty(Testimonial::class, 'temoignages', fn () => $this->seedTestimonials());
        $this->seedIfEmpty(GalleryImage::class, 'galerie', fn () => $this->seedGallery());
    }

    private function seedIfEmpty(string $model, string $libelle, callable $action): void
    {
        if ($model::query()->exists()) {
            $this->command?->getOutput()->writeln("    <comment>[conserve] $libelle deja rempli.</comment>");

            return;
        }

        $action();
    }

    private function seedUser(): void
    {
        // Le mot de passe n'est pose qu'a la creation : relancer le seeder ne doit
        // pas reinitialiser le mot de passe choisi par le proprietaire.
        User::firstOrCreate(
            ['email' => 'geraldoagonse@gmail.com'],
            [
                'name' => 'Géraldo Perridys AGONSE',
                'password' => 'Geraldo@2026',
                'is_admin' => true,
            ]
        );
    }

    private function seedSettings(): void
    {
        $settings = [
            'name' => 'Géraldo Perridys AGONSE',
            'role' => 'Formateur',
            'tagline' => 'Développer les compétences. Optimiser la performance. Transformer les pratiques.',
            'hero_text' => 'Formateur professionnel basé entre le Bénin et le Togo, j\'accompagne les entreprises et les ONG dans le développement des compétences de leurs équipes grâce à des formations pratiques, engageantes et orientées résultats.',
            'hero_photo' => '',
            'about_parcours' => 'Formateur de terrain, j\'exerce depuis plusieurs années au Bénin et au Togo, au service des entreprises, des institutions et des ONG. Mon parcours allie une solide formation académique en droit (Master) et une expertise concrète en marketing, vente et fidélisation, ce qui me permet de comprendre à la fois les enjeux juridiques, commerciaux et humains des organisations.',
            'about_approche' => 'Ma méthode repose sur la pédagogie active : études de cas, mises en situation, ateliers pratiques et plans d\'action immédiatement applicables. Chaque formation est adaptée au contexte et aux réalités de l\'organisation pour maximiser l\'impact sur le terrain.',
            'about_expertise' => 'Gestion du temps et des priorités, productivité professionnelle, vente et techniques commerciales, fidélisation de la clientèle. Des domaines complémentaires qui forment un socle complet de la performance commerciale et organisationnelle.',
            // Une qualification par ligne : la page « À propos » les affiche en liste.
            'about_qualifications' => implode("\n", [
                'Master en droit',
                'Certificat de formateur professionnel',
                'Compétences en marketing',
                'Compétences en vente et techniques commerciales',
                'Compétences en fidélisation de la clientèle',
                'Facilitateur certifié en pédagogie des adultes',
            ]),
            'about_photo' => '',
            'whatsapp' => '+229 01 67 20 00 02',
            'whatsapp_display' => '+229 01 67 20 00 02',
            'whatsapp_message' => 'Bonjour Géraldo Perridys AGONSE, je souhaite avoir plus d\'informations sur vos formations.',
            'phone' => '+229 98 54 54 14',
            'phone_display' => '+229 98 54 54 14',
            'email' => 'geraldoagonse@gmail.com',
            'location' => 'Bénin · Togo',
            'cta_title' => 'Prêt à booster la performance de vos équipes ?',
            'cta_text' => 'Contactez-moi dès aujourd\'hui pour discuter de vos besoins en formation. Une réponse vous sera apportée rapidement.',
            'seo_title' => 'Géraldo Perridys AGONSE – Formateur professionnel | Gestion du temps, Vente, Fidélisation',
            'seo_description' => 'Formateur professionnel au Bénin et au Togo. Formations en gestion du temps, productivité, vente et fidélisation client pour entreprises et ONG.',
            'seo_keywords' => 'formateur au Bénin, formateur au Togo, formation professionnelle, formation entreprise, gestion du temps, productivité, vente, fidélisation clientèle',
            'gallery_title' => 'Galerie photos',
            'gallery_text' => 'Quelques moments forts de mes interventions en entreprise.',
        ];

        // Les reglages modifiables depuis l'administration ne sont jamais ecrases.
        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => (string) $value]);
        }
    }

    private function seedDomains(): void
    {
        Domain::create([
            'title' => 'Gestion du temps et des priorités',
            'description' => 'Des méthodes éprouvées pour reprendre le contrôle de votre agenda, hiérarchiser vos tâches et éliminer les voleurs de temps.',
            'icon' => 'clock',
            'sort_order' => 1,
        ]);

        Domain::create([
            'title' => 'Productivité professionnelle',
            'description' => 'Organisez vos journées, vos outils et vos équipes pour produire plus de valeur avec moins de stress et d\'efforts inutiles.',
            'icon' => 'chart',
            'sort_order' => 2,
        ]);

        Domain::create([
            'title' => 'Vente et techniques commerciales',
            'description' => 'Maîtrisez l\'art de convaincre : prospection, argumentation, négociation et conclusion des ventes en toute confiance.',
            'icon' => 'rocket',
            'sort_order' => 3,
        ]);

        Domain::create([
            'title' => 'Fidélisation de la clientèle',
            'description' => 'Transformez vos clients en ambassadeurs grâce à une expérience client irréprochable et des stratégies de fidélisation durables.',
            'icon' => 'heart',
            'sort_order' => 4,
        ]);
    }

    private function seedReasons(): void
    {
        Reason::create([
            'title' => 'Pédagogie active',
            'description' => 'Des formations pratiques fondées sur des cas réels, des mises en situation et des exercices directement transposables au travail.',
            'icon' => 'sparkles',
            'sort_order' => 1,
        ]);

        Reason::create([
            'title' => 'Expérience terrain',
            'description' => 'Une expérience éprouvée auprès des entreprises et des ONG au Bénin et au Togo, dans des contextes économiques et culturels réels.',
            'icon' => 'briefcase',
            'sort_order' => 2,
        ]);

        Reason::create([
            'title' => 'Formations sur mesure',
            'description' => 'Chaque programme est adapté à votre secteur, à votre culture d\'entreprise et à vos objectifs précis.',
            'icon' => 'cog',
            'sort_order' => 3,
        ]);

        Reason::create([
            'title' => 'Résultats mesurables',
            'description' => 'Des plans d\'action concrets et un suivi post-formation pour garantir un impact visible sur la performance de vos équipes.',
            'icon' => 'target',
            'sort_order' => 4,
        ]);
    }

    private function seedFormations(): void
    {
        Formation::create([
            'slug' => 'gestion-du-temps-et-des-priorites',
            'title' => 'Gestion du temps et des priorités',
            'subtitle' => 'Reprenez le contrôle de votre journée de travail',
            'description' => 'Une formation pratique pour apprendre à planifier efficacement, hiérarchiser les priorités, gérer les interruptions et dire non aux tâches à faible valeur ajoutée.',
            'objectives' => [
                'Identifier ses propres voleurs de temps et les neutraliser',
                'Maîtriser les méthodes de priorisation (Matrice d\'Eisenhower, Pareto, Time Blocking)',
                'Planifier des journées et des semaines productives',
                'Apprendre à déléguer et à fixer des limites',
                'Réduire le stress lié à la surcharge de travail',
            ],
            'public_cible' => 'Managers, chefs de projets, professionnels et toute personne souhaitant mieux organiser son travail.',
            'duree' => '1 à 2 jours (7 heures par jour)',
            'modalites' => 'En présentiel ou à distance · Formation intra-entreprise ou interentreprises · Support de cours et exercices pratiques inclus',
            'programme' => [
                'Module 1 — Les fondamentaux de la gestion du temps : perception du temps, objectifs et planification stratégique',
                'Module 2 — Les méthodes de priorisation : matrice d\'Eisenhower, loi de Pareto, bloqueurs de temps',
                'Module 3 — Les voleurs de temps : interruptions, e-mails, réunions, procrastination',
                'Module 4 — Organiser son environnement de travail : outils numériques, délégation, automatisation',
                'Module 5 — Résistance au changement et maintien des acquis · Plans d\'action personnalisés',
            ],
            'icon' => 'clock',
            'sort_order' => 1,
        ]);

        Formation::create([
            'slug' => 'productivite-professionnelle',
            'title' => 'Productivité professionnelle',
            'subtitle' => 'Produire plus de valeur, avec moins de stress',
            'description' => 'Développez des routines et des systèmes efficaces pour améliorer durablement votre productivité individuelle et celle de vos équipes.',
            'objectives' => [
                'Structurer ses journées autour de priorités à fort impact',
                'Utiliser des outils de gestion de tâches et de projets adaptés',
                'Éliminer les activités à faible valeur ajoutée',
                'Mettre en place des routines de travail efficaces',
                'Mesurer et améliorer sa productivité dans la durée',
            ],
            'public_cible' => 'Équipes opérationnelles, collaborateurs, managers souhaitant améliorer la performance de leur service.',
            'duree' => '1 jour (7 heures) ou 2 demi-journées',
            'modalites' => 'En présentiel ou à distance · Ateliers pratiques et outils concrets fournis · Suivi post-formation',
            'programme' => [
                'Module 1 — Les leviers de la productivité : énergie, focus, organisation',
                'Module 2 — Fixer des objectifs SMART et les transformer en plans d\'action',
                'Module 3 — Gestion des tâches : méthodes Getting Things Done, Kanban, Eisenhower',
                'Module 4 — Réunionite, e-mails et notifications : reprendre la main',
                'Module 5 — Routines matin/soir, deep work et suivi de progression',
            ],
            'icon' => 'chart',
            'sort_order' => 2,
        ]);

        Formation::create([
            'slug' => 'vente-et-techniques-commerciales',
            'title' => 'Vente et techniques commerciales',
            'subtitle' => 'Devenez un commercial qui conclut',
            'description' => 'Un programme complet pour maîtriser les étapes de la vente : de la prospection à la conclusion, en passant par l\'argumentation et le traitement des objections.',
            'objectives' => [
                'Prospecter efficacement et qualifier ses clients',
                'Structurer un entretien de vente (méthode SIMAC / AIDA)',
                'Argumenter en se concentrant sur les bénéfices client',
                'Traiter les objections sans perdre en confiance',
                'Conclure la vente et développer le portefeuille client',
            ],
            'public_cible' => 'Commerciaux, conseillers clientèle, agents de vente, chefs des ventes et entrepreneurs.',
            'duree' => '2 jours (7 heures par jour)',
            'modalites' => 'En présentiel · Mises en situation filmées et débriefées · Exercices de jeux de rôle tout au long du parcours',
            'programme' => [
                'Module 1 — Les fondamentaux de la vente : posture du vendeur-conseil, éthique commerciale',
                'Module 2 — Prospection et qualification : sources de prospects, approche, prise de rendez-vous',
                'Module 3 — L\'entretien de vente : accueil, découverte des besoins, écoute active',
                'Module 4 — Argumentation et traitement des objections : bénéfices, preuves, prix',
                'Module 5 — Conclusion et suivi : techniques de closing, fidélisation et développement du portefeuille',
            ],
            'icon' => 'rocket',
            'sort_order' => 3,
        ]);

        Formation::create([
            'slug' => 'fidelisation-de-la-clientele',
            'title' => 'Fidélisation de la clientèle',
            'subtitle' => 'Transformez vos clients en ambassadeurs',
            'description' => 'Comprenez ce qui crée la fidélité et apprenez à bâtir une expérience client remarquable qui fidélise durablement et génère du bouche-à-oreille.',
            'objectives' => [
                'Comprendre les mécanismes de la fidélité et de la valeur client',
                'Construire un parcours client fluide et mémorable',
                'Gérer la relation client et les réclamations avec professionnalisme',
                'Mettre en place des programmes de fidélisation efficaces',
                'Mesurer la satisfaction et le taux de fidélité',
            ],
            'public_cible' => 'Équipes commerciales, services client, responsables qualité et relation client.',
            'duree' => '1 à 2 jours (7 heures par jour)',
            'modalites' => 'En présentiel ou à distance · Cas pratiques sur la base des vrais clients de l\'entreprise · Supports et outils fournis',
            'programme' => [
                'Module 1 — La fidélité client : enjeux économiques, mesure (NPS, RFM) et mécanismes psychologiques',
                'Module 2 — L\'expérience client : parcours client, points de contact, qualité de service',
                'Module 3 — Communication et relation client : écoute active, gestion des réclamations, service après-vente',
                'Module 4 — Programmes de fidélisation : avantages, parrainage, clubs privilèges, expériences personnalisées',
                'Module 5 — La marque et les ambassadeurs : donner envie de recommander · Plan d\'action fidélité',
            ],
            'icon' => 'heart',
            'sort_order' => 4,
        ]);
    }

    private function seedServices(): void
    {
        Service::create([
            'title' => 'Formation intra-entreprise',
            'description' => 'Des formations dispensées directement au sein de votre organisation, sur site ou dans vos locaux, pour vos équipes, quels que soient leur effectif et leur niveau.',
            'icon' => 'building',
            'sort_order' => 1,
        ]);

        Service::create([
            'title' => 'Ateliers pratiques',
            'description' => 'Des ateliers courts et interactifs (2 à 4 heures) pour aborder une thématique précise avec des mises en situation immédiatement exploitables.',
            'icon' => 'tools',
            'sort_order' => 2,
        ]);

        Service::create([
            'title' => 'Formations personnalisées',
            'description' => 'Des programmes conçus sur mesure à partir de vos objectifs, de votre secteur d\'activité et de vos problématiques spécifiques.',
            'icon' => 'pencil',
            'sort_order' => 3,
        ]);

        Service::create([
            'title' => 'Formations d\'équipes commerciales',
            'description' => 'Renforcez les savoir-faire de vos équipes terrain : techniques de vente, négociation, prospection et fidélisation, pour des résultats commerciaux mesurables.',
            'icon' => 'users',
            'sort_order' => 4,
        ]);
    }

    private function seedExperiences(): void
    {
        Experience::create([
            'title' => 'Animation de formations au Bénin',
            'description' => 'Conception et animation de programmes de formation en gestion du temps, productivité et techniques de vente auprès de PME, d\'institutions et d\'ONG au Bénin.',
            'icon' => 'map',
            'sort_order' => 1,
        ]);

        Experience::create([
            'title' => 'Interventions au Togo',
            'description' => 'Déploiement de formations commerciales et de programmes de fidélisation client pour des équipes opérationnelles et des managers au Togo.',
            'icon' => 'map',
            'sort_order' => 2,
        ]);

        Experience::create([
            'title' => 'Accompagnement d\'équipes commerciales',
            'description' => 'Renforcement des compétences de vente et de relation client d\'équipes commerciales dans divers secteurs : distribution, services, banque et microfinance.',
            'icon' => 'users',
            'sort_order' => 3,
        ]);

        Experience::create([
            'title' => 'Formation de formateurs internes',
            'description' => 'Transmission d\'une pédagogie active afin que les organisations puissent internaliser durablement le développement des compétences de leurs équipes.',
            'icon' => 'graduation',
            'sort_order' => 4,
        ]);

        Experience::create([
            'title' => 'Conseil en organisation et productivité',
            'description' => 'Audit des pratiques organisationnelles et recommandations concrètes pour améliorer la productivité individuelle et collective.',
            'icon' => 'cog',
            'sort_order' => 5,
        ]);
    }

    private function seedTestimonials(): void
    {
        Testimonial::create([
            'author' => 'A. K.',
            'fonction' => 'Directrice des RH, ONG internationale (Cotonou)',
            'content' => 'Des formations concrètes, centrées sur nos réalités. Nos équipes ont immédiatement réorganisé leur travail, et la productivité s\'est nettement améliorée.',
            'photo' => '',
            'sort_order' => 1,
        ]);

        Testimonial::create([
            'author' => 'M. S.',
            'fonction' => 'Responsable commercial, entreprise de distribution (Lomé)',
            'content' => 'La formation en techniques de vente a transformé la posture de nos commerciaux. Les résultats sur le terrain se sont fait sentir en quelques semaines.',
            'photo' => '',
            'sort_order' => 2,
        ]);

        Testimonial::create([
            'author' => 'F. D.',
            'fonction' => 'Chef de service, établissement financier (Bénin)',
            'content' => 'Un formateur à l\'écoute, pédagogue et professionnel. Les mises en situation nous ont permis de corriger des pratiques installées depuis des années.',
            'photo' => '',
            'sort_order' => 3,
        ]);

        Testimonial::create([
            'author' => 'R. T.',
            'fonction' => 'Coordinatrice de programmes, ONG (Togo)',
            'content' => 'Le module sur la gestion des priorités a changé la manière dont nous pilotons nos projets. Un vrai appui pour notre ONG, avec des outils que nous utilisons tous les jours.',
            'photo' => '',
            'sort_order' => 4,
        ]);
    }

    private function seedGallery(): void
    {
        $images = [
            'Session de formation en présentiel',
            'Atelier pratique en entreprise',
            'Formation spéciale vente et négociation',
            'Travaux de groupe et mises en situation',
            'Remise des attestations de formation',
            'Séminaire de développement des compétences',
        ];

        foreach ($images as $index => $caption) {
            GalleryImage::create([
                'image_path' => 'uploads/placeholder.svg',
                'caption' => $caption,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
