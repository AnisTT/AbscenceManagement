<?php

namespace App\DataFixtures;

use App\Entity\Absence;
use App\Entity\Classe;
use App\Entity\Etudiant;
use App\Entity\Justification;
use App\Entity\Matiere;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // ===== UTILISATEURS =====
        $admin = new User();
        $admin->setEmail('admin@gestabsences.com');
        $admin->setNom('Dupont');
        $admin->setPrenom('Jean');
        $admin->setRoles([User::ROLE_ADMIN]);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $agent = new User();
        $agent->setEmail('agent@gestabsences.com');
        $agent->setNom('Martin');
        $agent->setPrenom('Marie');
        $agent->setRoles([User::ROLE_AGENT]);
        $agent->setPassword($this->passwordHasher->hashPassword($agent, 'agent123'));
        $manager->persist($agent);

        // ===== CLASSES =====
        $classesData = [
            ['nom' => 'L1 Informatique', 'niveau' => 'L1', 'filiere' => 'Informatique'],
            ['nom' => 'L2 Informatique', 'niveau' => 'L2', 'filiere' => 'Informatique'],
            ['nom' => 'L3 Informatique', 'niveau' => 'L3', 'filiere' => 'Informatique'],
            ['nom' => 'M1 Informatique', 'niveau' => 'M1', 'filiere' => 'Informatique'],
            ['nom' => 'M2 Informatique', 'niveau' => 'M2', 'filiere' => 'Informatique'],
            ['nom' => 'L1 Mathématiques', 'niveau' => 'L1', 'filiere' => 'Mathématiques'],
            ['nom' => 'L2 Mathématiques', 'niveau' => 'L2', 'filiere' => 'Mathématiques'],
        ];

        $classes = [];
        foreach ($classesData as $data) {
            $classe = new Classe();
            $classe->setNom($data['nom']);
            $classe->setNiveau($data['niveau']);
            $classe->setFiliere($data['filiere']);
            $manager->persist($classe);
            $classes[] = $classe;
        }

        // ===== MATIÈRES =====
        $matieresData = [
            ['nom' => 'Algorithmique', 'code' => 'ALGO'],
            ['nom' => 'Programmation Web', 'code' => 'WEB'],
            ['nom' => 'Base de données', 'code' => 'BDD'],
            ['nom' => 'Réseaux', 'code' => 'RES'],
            ['nom' => 'Analyse', 'code' => 'ANA'],
            ['nom' => 'Algèbre', 'code' => 'ALG'],
            ['nom' => 'Systèmes d\'exploitation', 'code' => 'SYS'],
            ['nom' => 'Intelligence Artificielle', 'code' => 'IA'],
        ];

        $matieres = [];
        foreach ($matieresData as $data) {
            $matiere = new Matiere();
            $matiere->setNom($data['nom']);
            $matiere->setCode($data['code']);
            $matiere->setCoefficient(rand(1, 4));
            $manager->persist($matiere);
            $matieres[] = $matiere;
        }

        // ===== ÉTUDIANTS =====
        $prenomsM = ['Ali', 'Mohamed', 'Karim', 'Omar', 'Youssef', 'Ahmed', 'Mehdi', 'Bilal', 'Amine', 'Hamza'];
        $prenomsF = ['Sara', 'Fatima', 'Nadia', 'Amina', 'Leila', 'Khadija', 'Zineb', 'Salma', 'Meryem', 'Houda'];
        $noms = ['Benali', 'Tazi', 'Alaoui', 'Idrissi', 'Fassi', 'Berrada', 'Chraibi', 'Benjelloun', 'Kettani', 'Lahlou'];

        $etudiants = [];
        $matriculeCounter = 1000;

        foreach ($classes as $classe) {
            // 15-25 étudiants par classe
            $nbEtudiants = rand(15, 25);
            
            for ($i = 0; $i < $nbEtudiants; $i++) {
                $etudiant = new Etudiant();
                
                $isFemale = rand(0, 1);
                $prenom = $isFemale 
                    ? $prenomsF[array_rand($prenomsF)] 
                    : $prenomsM[array_rand($prenomsM)];
                $nom = $noms[array_rand($noms)];
                
                $etudiant->setPrenom($prenom);
                $etudiant->setNom($nom);
                $etudiant->setMatricule('ETU' . $matriculeCounter++);
                $etudiant->setEmail(strtolower($prenom . '.' . $nom . $matriculeCounter) . '@univ.edu');
                $etudiant->setDateNaissance(new \DateTime(sprintf('-%d years', rand(18, 25))));
                $etudiant->setClasse($classe);
                
                $manager->persist($etudiant);
                $etudiants[] = $etudiant;
            }
        }

        // ===== ABSENCES =====
        $absences = [];
        $heures = [
            ['debut' => '08:00', 'fin' => '10:00'],
            ['debut' => '10:15', 'fin' => '12:15'],
            ['debut' => '14:00', 'fin' => '16:00'],
            ['debut' => '16:15', 'fin' => '18:15'],
        ];

        // Générer des absences sur les 3 derniers mois
        for ($i = 0; $i < 150; $i++) {
            $absence = new Absence();
            
            $etudiant = $etudiants[array_rand($etudiants)];
            $matiere = $matieres[array_rand($matieres)];
            
            // Date aléatoire dans les 90 derniers jours
            $daysAgo = rand(0, 90);
            $dateAbsence = new \DateTime("-{$daysAgo} days");
            
            // Éviter les week-ends
            while (in_array($dateAbsence->format('N'), [6, 7])) {
                $dateAbsence->modify('-1 day');
            }
            
            $heure = $heures[array_rand($heures)];
            
            $absence->setEtudiant($etudiant);
            $absence->setMatiere($matiere);
            $absence->setDateAbsence($dateAbsence);
            $absence->setHeureDebut(new \DateTime($heure['debut']));
            $absence->setHeureFin(new \DateTime($heure['fin']));
            $absence->setCreatedAt(new \DateTime("-{$daysAgo} days +2 hours"));
            
            // 40% des absences sont justifiées
            $isJustifiee = rand(1, 100) <= 40;
            $absence->setJustifiee($isJustifiee);
            
            $manager->persist($absence);
            $absences[] = $absence;
        }

        // ===== JUSTIFICATIONS =====
        $justificationsDescriptions = [
            'Maladie - certificat médical fourni',
            'Rendez-vous médical prévu',
            'Problème de transport (grève)',
            'Décès dans la famille',
            'Convocation administrative',
            'Examen dans une autre filière',
            'Problème de santé personnel',
            'Accident de la route',
        ];

        // Créer des justifications pour certaines absences
        foreach ($absences as $absence) {
            // 60% des absences non justifiées ont une demande de justification
            if (!$absence->isJustifiee() && rand(1, 100) <= 60) {
                $justification = new Justification();
                $justification->setAbsence($absence);
                $justification->setDescription($justificationsDescriptions[array_rand($justificationsDescriptions)]);
                
                $dateSoumission = clone $absence->getDateAbsence();
                $dateSoumission->modify('+' . rand(1, 3) . ' days');
                $justification->setDateSoumission($dateSoumission);
                
                // 50% en attente, 30% validées, 20% refusées
                $rand = rand(1, 100);
                if ($rand <= 50) {
                    $justification->setStatut(Justification::STATUT_EN_ATTENTE);
                } elseif ($rand <= 80) {
                    $justification->setStatut(Justification::STATUT_VALIDEE);
                    $justification->setValidePar($admin);
                    $justification->setDateValidation(new \DateTime('-' . rand(1, 10) . ' days'));
                    $justification->setCommentaireValidation('Justification acceptée');
                    $absence->setJustifiee(true);
                } else {
                    $justification->setStatut(Justification::STATUT_REFUSEE);
                    $justification->setValidePar($admin);
                    $justification->setDateValidation(new \DateTime('-' . rand(1, 10) . ' days'));
                    $justification->setCommentaireValidation('Pièce justificative non recevable');
                }
                
                $manager->persist($justification);
            }
        }

        $manager->flush();
    }
}
