<?php

namespace App\Controller;

use App\Entity\Absence;
use App\Repository\AbsenceRepository;
use App\Repository\EtudiantRepository;
use App\Repository\JustificationRepository;
use App\Repository\ClasseRepository;
use App\Repository\MatiereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    public function __construct(
        private EtudiantRepository $etudiantRepository,
        private AbsenceRepository $absenceRepository,
        private JustificationRepository $justificationRepository,
        private ClasseRepository $classeRepository,
        private MatiereRepository $matiereRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'admin_dashboard')]
    #[Route('/dashboard', name: 'admin_dashboard_alt')]
    public function index(): Response
    {
        // ===== STATISTIQUES KPI =====
        $totalEtudiants = $this->etudiantRepository->countAll();
        $totalAbsences = $this->absenceRepository->countAll();
        $justificationsEnAttente = $this->justificationRepository->countEnAttente();
        
        // Calcul du taux d'absentéisme
        // Formule: (Nombre d'absences / (Nombre d'étudiants * Nombre de jours ouvrés)) * 100
        // Simplifié ici: on utilise un ratio absences/étudiants
        $tauxAbsenteisme = $totalEtudiants > 0 
            ? round(($totalAbsences / ($totalEtudiants * 20)) * 100, 1) // 20 jours de cours estimés
            : 0;

        // ===== DONNÉES GRAPHIQUE =====
        $absencesParClasse = $this->absenceRepository->countByClasse();
        
        // Préparer les données pour Chart.js
        $chartLabels = array_map(fn($item) => $item['classe'], $absencesParClasse);
        $chartData = array_map(fn($item) => $item['total'], $absencesParClasse);

        // ===== DERNIÈRES ABSENCES =====
        $dernieresAbsences = $this->absenceRepository->findLatest(10);

        // ===== STATISTIQUES SUPPLÉMENTAIRES =====
        $absencesCeMois = $this->absenceRepository->countThisMonth();
        $absencesCetteSemaine = $this->absenceRepository->countThisWeek();
        $absencesNonJustifiees = $this->absenceRepository->countNonJustifiees();

        return $this->render('admin/dashboard.html.twig', [
            // KPI Cards
            'totalEtudiants' => $totalEtudiants,
            'totalAbsences' => $totalAbsences,
            'justificationsEnAttente' => $justificationsEnAttente,
            'tauxAbsenteisme' => $tauxAbsenteisme,
            
            // Chart data
            'chartLabels' => json_encode($chartLabels),
            'chartData' => json_encode($chartData),
            
            // Table data
            'dernieresAbsences' => $dernieresAbsences,
            
            // Extra stats
            'absencesCeMois' => $absencesCeMois,
            'absencesCetteSemaine' => $absencesCetteSemaine,
            'absencesNonJustifiees' => $absencesNonJustifiees,
        ]);
    }

    #[Route('/absences', name: 'admin_absences', methods: ['GET'])]
    public function absences(): Response
    {
        $absences = $this->absenceRepository->findLatest(50);
        $etudiants = $this->etudiantRepository->findAll();
        $matieres = $this->matiereRepository->findAll();
        
        return $this->render('admin/absences.html.twig', [
            'absences' => $absences,
            'etudiants' => $etudiants,
            'matieres' => $matieres,
        ]);
    }

    #[Route('/absences/new', name: 'admin_absences_new', methods: ['POST'])]
    public function newAbsence(Request $request): Response
    {
        $etudiantId = $request->request->get('etudiant');
        $matiereId = $request->request->get('matiere');
        $dateAbsence = $request->request->get('date');
        $heureDebut = $request->request->get('heure_debut');
        $heureFin = $request->request->get('heure_fin');
        $motif = $request->request->get('motif');

        $etudiant = $this->etudiantRepository->find($etudiantId);
        $matiere = $this->matiereRepository->find($matiereId);

        if (!$etudiant || !$matiere) {
            $this->addFlash('error', 'Étudiant ou matière invalide.');
            return $this->redirectToRoute('admin_absences');
        }

        $absence = new Absence();
        $absence->setEtudiant($etudiant);
        $absence->setMatiere($matiere);
        $absence->setDateAbsence(new \DateTime($dateAbsence));
        
        if ($heureDebut) {
            $absence->setHeureDebut(new \DateTime($heureDebut));
        }
        if ($heureFin) {
            $absence->setHeureFin(new \DateTime($heureFin));
        }
        if ($motif) {
            $absence->setMotif($motif);
        }

        $this->entityManager->persist($absence);
        $this->entityManager->flush();

        $this->addFlash('success', 'Absence enregistrée avec succès.');
        return $this->redirectToRoute('admin_absences');
    }

    #[Route('/justifications', name: 'admin_justifications')]
    public function justifications(): Response
    {
        $justificationsEnAttente = $this->justificationRepository->findEnAttente();
        $justificationsTraitees = $this->justificationRepository->findLatestProcessed(10);
        
        return $this->render('admin/justifications.html.twig', [
            'justificationsEnAttente' => $justificationsEnAttente,
            'justificationsTraitees' => $justificationsTraitees,
        ]);
    }

    #[Route('/rapport', name: 'admin_rapport')]
    public function rapport(): Response
    {
        // Page de génération de rapport
        $classes = $this->classeRepository->findAll();
        $statsParClasse = $this->absenceRepository->countByClasse();
        $statsMensuelles = $this->absenceRepository->getMonthlyStats();
        
        return $this->render('admin/rapport.html.twig', [
            'classes' => $classes,
            'statsParClasse' => $statsParClasse,
            'statsMensuelles' => $statsMensuelles,
        ]);
    }
}
