<?php

#[CMiddleware(middleware: ['auth'])]
class SubscriptionController extends Controller
{
    // ========================
    // PAGES (Views)
    // ========================

    #[CRoute('/subscriptions', CHTTPMethod::GET)]
    public function index(): void
    {
        $this->view('subscription/index', [
            'title' => 'Abonnements',
        ]);
    }

    #[CRoute('/subscriptions/create', CHTTPMethod::GET)]
    public function create(): void
    {
        $this->view('subscription/create', [
            'title' => 'Ajouter un abonnement',
        ]);
    }

    #[CRoute('/subscriptions/{id}/edit', CHTTPMethod::GET)]
    public function edit(string $id): void
    {
        $this->view('subscription/edit', [
            'title' => 'Modifier l\'abonnement',
            'subscriptionId' => $id,
        ]);
    }

    // ========================
    // API CRUD (REST)
    // ========================

    #[CRoute('/api/subscriptions', CHTTPMethod::GET)]
    public function apiGetSubscriptions(): void
    {
        $conn = Model::getConnection();
        $stmt = $conn->prepare('SELECT * FROM Subscription WHERE admin_id = :admin_id ORDER BY name ASC');
        $stmt->execute(['admin_id' => $_SESSION['user']['id']]);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->json(['code' => 200, 'data' => $subscriptions]);
    }

    #[CRoute('/api/subscriptions/stats', CHTTPMethod::GET)]
    public function apiGetStats(): void
    {
        $this->json([
            'code' => 200,
            'data' => $this->getStats()
        ]);
    }

    #[CRoute('/api/subscriptions/{id}', CHTTPMethod::GET)]
    public function apiGetSubscription(string $id): void
    {
        $conn = Model::getConnection();
        $stmt = $conn->prepare('SELECT * FROM Subscription WHERE id = :id AND admin_id = :admin_id');
        $stmt->execute(['id' => $id, 'admin_id' => $_SESSION['user']['id']]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$subscription) {
            $this->json(['code' => 404, 'error' => 'Abonnement non trouvé']);
            return;
        }
        
        $this->json(['code' => 200, 'data' => $subscription]);
    }

    #[CRoute('/api/subscriptions', CHTTPMethod::POST)]
    public function apiCreateSubscription(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['name']) || empty($data['amount'])) {
            $this->json(['code' => 400, 'error' => 'Nom et montant requis']);
            return;
        }

        $conn = Model::getConnection();
        $stmt = $conn->prepare('
            INSERT INTO Subscription (admin_id, name, amount, type, type_period, day_of_month, day_of_week, date_of_year) 
            VALUES (:admin_id, :name, :amount, :type, :type_period, :day_of_month, :day_of_week, :date_of_year)
        ');
        
        $stmt->execute([
            'admin_id' => $_SESSION['user']['id'],
            'name' => $data['name'],
            'amount' => abs((float)$data['amount']),
            'type' => $data['type'] ?? 'expense',
            'type_period' => $data['type_period'] ?? 'monthly',
            'day_of_month' => $data['day_of_month'] ?? null,
            'day_of_week' => $data['day_of_week'] ?? null,
            'date_of_year' => $data['date_of_year'] ?? null,
        ]);

        $this->json(['code' => 201, 'data' => ['id' => $conn->lastInsertId()], 'message' => 'Abonnement créé']);
    }

    #[CRoute('/api/subscriptions/{id}', CHTTPMethod::PUT)]
    public function apiUpdateSubscription(string $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $conn = Model::getConnection();
        
        // Vérifier que l'abonnement appartient à l'utilisateur
        $stmt = $conn->prepare('SELECT id FROM Subscription WHERE id = :id AND admin_id = :admin_id');
        $stmt->execute(['id' => $id, 'admin_id' => $_SESSION['user']['id']]);
        if (!$stmt->fetch()) {
            $this->json(['code' => 404, 'error' => 'Abonnement non trouvé']);
            return;
        }

        $stmt = $conn->prepare('
            UPDATE Subscription 
            SET name = :name, amount = :amount, type = :type, type_period = :type_period, 
                day_of_month = :day_of_month, day_of_week = :day_of_week, date_of_year = :date_of_year
            WHERE id = :id AND admin_id = :admin_id
        ');
        
        $stmt->execute([
            'id' => $id,
            'admin_id' => $_SESSION['user']['id'],
            'name' => $data['name'],
            'amount' => abs((float)$data['amount']),
            'type' => $data['type'] ?? 'expense',
            'type_period' => $data['type_period'] ?? 'monthly',
            'day_of_month' => $data['day_of_month'] ?? null,
            'day_of_week' => $data['day_of_week'] ?? null,
            'date_of_year' => $data['date_of_year'] ?? null,
        ]);

        $this->json(['code' => 200, 'message' => 'Abonnement modifié']);
    }

    #[CRoute('/api/subscriptions/{id}', CHTTPMethod::DELETE)]
    public function apiDeleteSubscription(string $id): void
    {
        $conn = Model::getConnection();
        
        // Vérifier que l'abonnement appartient à l'utilisateur
        $stmt = $conn->prepare('SELECT id FROM Subscription WHERE id = :id AND admin_id = :admin_id');
        $stmt->execute(['id' => $id, 'admin_id' => $_SESSION['user']['id']]);
        if (!$stmt->fetch()) {
            $this->json(['code' => 404, 'error' => 'Abonnement non trouvé']);
            return;
        }

        $stmt = $conn->prepare('DELETE FROM Subscription WHERE id = :id AND admin_id = :admin_id');
        $stmt->execute(['id' => $id, 'admin_id' => $_SESSION['user']['id']]);

        $this->json(['code' => 200, 'message' => 'Abonnement supprimé']);
    }

    public function getStats(): array
    {
        $conn = Model::getConnection();
        $adminId = $_SESSION['user']['id'];
        
        $today = new DateTime();
        $currentDay = (int)$today->format('j');
        $currentDayOfWeek = (int)$today->format('N'); // 1=Lundi, 7=Dimanche
        $currentMonth = (int)$today->format('n');
        $currentYear = (int)$today->format('Y');
        $daysInMonth = (int)$today->format('t');
        $startOfMonth = $today->format('Y-m-01');
        
        // Récupérer tous les abonnements
        $stmt = $conn->prepare('SELECT * FROM Subscription WHERE admin_id = :admin_id');
        $stmt->execute(['admin_id' => $adminId]);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculer les totaux
        $totalMonthlyIncome = 0;
        $totalMonthlyExpense = 0;
        $paidThisMonthIncome = 0;
        $paidThisMonthExpense = 0;
        $remainingThisMonthIncome = 0;
        $remainingThisMonthExpense = 0;
        
        $paidSubscriptions = [];
        $remainingSubscriptions = [];
        
        foreach ($subscriptions as $sub) {
            $amount = (float)$sub['amount'];
            $type = $sub['type'];
            $period = $sub['type_period'];
            
            // Calculer le montant mensuel équivalent
            $monthlyAmount = match($period) {
                'weekly' => $amount * 4.33,
                'monthly' => $amount,
                'yearly' => $amount / 12,
                default => $amount
            };
            
            if ($type === 'income') {
                $totalMonthlyIncome += $monthlyAmount;
            } else {
                $totalMonthlyExpense += $monthlyAmount;
            }
            
            // Vérifier si l'abonnement a déjà été payé ce mois
            $isPaid = false;
            $nextDate = null;
            
            switch ($period) {
                case 'weekly':
                    // Pour les abonnements hebdomadaires, calculer combien ont été payés
                    $dayOfWeek = (int)$sub['day_of_week'];
                    $weeksPaid = 0;
                    $weeksRemaining = 0;
                    
                    // Compter les semaines depuis le début du mois
                    $checkDate = new DateTime($startOfMonth);
                    while ($checkDate <= $today) {
                        if ((int)$checkDate->format('N') === $dayOfWeek) {
                            $weeksPaid++;
                        }
                        $checkDate->modify('+1 day');
                    }
                    
                    // Compter les semaines restantes
                    $endOfMonth = new DateTime($today->format('Y-m-t'));
                    $checkDate = clone $today;
                    $checkDate->modify('+1 day');
                    while ($checkDate <= $endOfMonth) {
                        if ((int)$checkDate->format('N') === $dayOfWeek) {
                            $weeksRemaining++;
                        }
                        $checkDate->modify('+1 day');
                    }
                    
                    $paidAmount = $amount * $weeksPaid;
                    $remainingAmount = $amount * $weeksRemaining;
                    
                    if ($type === 'income') {
                        $paidThisMonthIncome += $paidAmount;
                        $remainingThisMonthIncome += $remainingAmount;
                    } else {
                        $paidThisMonthExpense += $paidAmount;
                        $remainingThisMonthExpense += $remainingAmount;
                    }
                    
                    $isPaid = $weeksPaid > 0;
                    break;
                    
                case 'monthly':
                    $dayOfMonth = (int)$sub['day_of_month'];
                    $isPaid = $currentDay >= $dayOfMonth;
                    
                    if ($isPaid) {
                        if ($type === 'income') {
                            $paidThisMonthIncome += $amount;
                        } else {
                            $paidThisMonthExpense += $amount;
                        }
                    } else {
                        if ($type === 'income') {
                            $remainingThisMonthIncome += $amount;
                        } else {
                            $remainingThisMonthExpense += $amount;
                        }
                    }
                    break;
                    
                case 'yearly':
                    $dateOfYear = $sub['date_of_year'];
                    if ($dateOfYear) {
                        $subDate = new DateTime($dateOfYear);
                        $subMonth = (int)$subDate->format('n');
                        $subDay = (int)$subDate->format('j');
                        
                        // Vérifier si c'est ce mois-ci
                        if ($subMonth === $currentMonth) {
                            $isPaid = $currentDay >= $subDay;
                            
                            if ($isPaid) {
                                if ($type === 'income') {
                                    $paidThisMonthIncome += $amount;
                                } else {
                                    $paidThisMonthExpense += $amount;
                                }
                            } else {
                                if ($type === 'income') {
                                    $remainingThisMonthIncome += $amount;
                                } else {
                                    $remainingThisMonthExpense += $amount;
                                }
                            }
                        }
                    }
                    break;
            }
            
            // Ajouter aux listes
            $subData = [
                'id' => $sub['id'],
                'name' => $sub['name'],
                'amount' => $amount,
                'type' => $type,
                'type_period' => $period,
                'day_of_month' => $sub['day_of_month'],
                'day_of_week' => $sub['day_of_week'],
                'date_of_year' => $sub['date_of_year'],
            ];
            
            if ($isPaid) {
                $paidSubscriptions[] = $subData;
            } else {
                $remainingSubscriptions[] = $subData;
            }
        }
        
        return [
            'period' => [
                'month' => $today->format('Y-m'),
                'current_day' => $currentDay,
                'days_in_month' => $daysInMonth,
            ],
            'monthly_totals' => [
                'income' => round($totalMonthlyIncome, 2),
                'expense' => round($totalMonthlyExpense, 2),
                'net' => round($totalMonthlyIncome - $totalMonthlyExpense, 2),
            ],
            'paid_this_month' => [
                'income' => round($paidThisMonthIncome, 2),
                'expense' => round($paidThisMonthExpense, 2),
                'net' => round($paidThisMonthIncome - $paidThisMonthExpense, 2),
            ],
            'remaining_this_month' => [
                'income' => round($remainingThisMonthIncome, 2),
                'expense' => round($remainingThisMonthExpense, 2),
                'net' => round($remainingThisMonthIncome - $remainingThisMonthExpense, 2),
            ],
            'paid_subscriptions' => $paidSubscriptions,
            'remaining_subscriptions' => $remainingSubscriptions,
            'all_subscriptions' => $subscriptions,
            'total_paid' => round($paidThisMonthIncome - $paidThisMonthExpense, 2),
            'total_remaining' => round($remainingThisMonthIncome - $remainingThisMonthExpense, 2),
            'total_monthly' => round($totalMonthlyIncome - $totalMonthlyExpense, 2)
        ];
    }
}
