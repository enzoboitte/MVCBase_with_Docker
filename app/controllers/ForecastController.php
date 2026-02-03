<?php

class ForecastController extends Controller
{
    /**
     * Prévision du mois en cours
     * Calcule: Solde actuel + Revenus restants - Factures fixes restantes - Budget variable restant
     */
    #[CRoute('/api/forecast/current-month', CHTTPMethod::GET, middleware: ['auth'])]
    public function currentMonth(): void
    {
        $userId = $_SESSION['user']['id'];
        $today = new DateTime();
        $endOfMonth = new DateTime('last day of this month');
        $startOfMonth = new DateTime('first day of this month');
        
        $conn = Model::getConnection();
        
        // 1. Solde global actuel de tous les comptes
        $stmt = $conn->prepare('
            SELECT SUM(current_balance) as total 
            FROM Account 
            WHERE user_id = :user_id AND include_in_net_worth = 1
        ');
        $stmt->execute(['user_id' => $userId]);
        $currentGlobalBalance = floatval($stmt->fetchColumn() ?: 0);
        
        // 2. Revenus récurrents restants ce mois (abonnements de type income)
        $stmt = $conn->prepare('
            SELECT COALESCE(SUM(amount), 0) as total
            FROM Subscription 
            WHERE user_id = :user_id 
                AND type = "income" 
                AND is_active = 1
                AND next_due_date > :today
                AND next_due_date <= :end_of_month
        ');
        $stmt->execute([
            'user_id' => $userId,
            'today' => $today->format('Y-m-d'),
            'end_of_month' => $endOfMonth->format('Y-m-d')
        ]);
        $projectedIncome = floatval($stmt->fetchColumn() ?: 0);
        
        // 3. Dépenses fixes restantes (abonnements de type expense)
        $stmt = $conn->prepare('
            SELECT COALESCE(SUM(amount), 0) as total
            FROM Subscription 
            WHERE user_id = :user_id 
                AND type = "expense" 
                AND is_active = 1
                AND next_due_date > :today
                AND next_due_date <= :end_of_month
        ');
        $stmt->execute([
            'user_id' => $userId,
            'today' => $today->format('Y-m-d'),
            'end_of_month' => $endOfMonth->format('Y-m-d')
        ]);
        $projectedFixedCosts = floatval($stmt->fetchColumn() ?: 0);
        
        // 4. Budget variable: estimation des dépenses restantes basée sur les catégories avec budget
        $daysInMonth = intval($endOfMonth->format('j'));
        $currentDay = intval($today->format('j'));
        $daysRemaining = $daysInMonth - $currentDay;
        
        // Récupérer les budgets et dépenses du mois
        $stmt = $conn->prepare('
            SELECT 
                c.id,
                c.name,
                c.budget_amount,
                COALESCE(SUM(t.amount), 0) as spent
            FROM Category c
            LEFT JOIN Transaction t ON t.category_id = c.id 
                AND t.type = "expense"
                AND DATE_FORMAT(t.date, "%Y-%m") = :month
            WHERE c.user_id = :user_id 
                AND c.type = "expense" 
                AND c.budget_amount IS NOT NULL
            GROUP BY c.id
        ');
        $stmt->execute([
            'user_id' => $userId,
            'month' => $today->format('Y-m')
        ]);
        $budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $projectedVariableSpend = 0;
        $budgetDetails = [];
        
        foreach ($budgets as $budget) {
            $budgetAmount = floatval($budget['budget_amount']);
            $spent = floatval($budget['spent']);
            $remaining = max(0, $budgetAmount - $spent);
            
            // Estimer les dépenses restantes: moyenne journalière * jours restants
            $avgDaily = $spent / max(1, $currentDay);
            $estimatedRemaining = min($remaining, $avgDaily * $daysRemaining);
            
            $projectedVariableSpend += $estimatedRemaining;
            
            $budgetDetails[] = [
                'name' => $budget['name'],
                'budget' => $budgetAmount,
                'spent' => $spent,
                'remaining' => $remaining,
                'estimated_remaining_spend' => round($estimatedRemaining, 2)
            ];
        }
        
        // 5. Calcul du solde estimé en fin de mois
        $estimatedEndBalance = $currentGlobalBalance + $projectedIncome - $projectedFixedCosts - $projectedVariableSpend;
        
        // 6. Déterminer le statut
        $status = 'safe';
        if ($estimatedEndBalance < 0) {
            $status = 'danger';
        } elseif ($estimatedEndBalance < 500) {
            $status = 'warning';
        }
        
        // 7. Historique du solde pour le graphique
        $stmt = $conn->prepare('
            SELECT 
                DATE(t.date) as day,
                SUM(CASE WHEN t.type = "income" THEN t.amount 
                         WHEN t.type = "expense" THEN -t.amount 
                         ELSE 0 END) as daily_change
            FROM Transaction t
            WHERE t.user_id = :user_id 
                AND DATE_FORMAT(t.date, "%Y-%m") = :month
            GROUP BY DATE(t.date)
            ORDER BY day
        ');
        $stmt->execute(['user_id' => $userId, 'month' => $today->format('Y-m')]);
        $dailyChanges = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Calculer le solde de départ du mois
        $stmt = $conn->prepare('
            SELECT SUM(current_balance) as total FROM Account WHERE user_id = :user_id AND include_in_net_worth = 1
        ');
        $stmt->execute(['user_id' => $userId]);
        $currentTotal = floatval($stmt->fetchColumn() ?: 0);
        
        // Reconstituer l'historique
        $balanceHistory = [];
        $runningBalance = $currentGlobalBalance;
        
        // Calculer le solde de début de mois en soustrayant les transactions du mois
        $stmt = $conn->prepare('
            SELECT COALESCE(SUM(CASE WHEN type = "income" THEN amount 
                                     WHEN type = "expense" THEN -amount 
                                     ELSE 0 END), 0) as total
            FROM Transaction 
            WHERE user_id = :user_id 
                AND DATE_FORMAT(date, "%Y-%m") = :month
        ');
        $stmt->execute(['user_id' => $userId, 'month' => $today->format('Y-m')]);
        $monthChange = floatval($stmt->fetchColumn() ?: 0);
        $startBalance = $currentGlobalBalance - $monthChange;
        
        $runningBalance = $startBalance;
        for ($day = 1; $day <= $currentDay; $day++) {
            $dateStr = $startOfMonth->format('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT);
            if (isset($dailyChanges[$dateStr])) {
                $runningBalance += floatval($dailyChanges[$dateStr]);
            }
            $balanceHistory[] = [
                'date' => $dateStr,
                'balance' => round($runningBalance, 2),
                'type' => 'actual'
            ];
        }
        
        // Projection future
        $projectedBalance = $runningBalance;
        $dailyVariableEstimate = $daysRemaining > 0 ? $projectedVariableSpend / $daysRemaining : 0;
        
        // Récupérer les abonnements à venir
        $stmt = $conn->prepare('
            SELECT next_due_date, type, amount 
            FROM Subscription 
            WHERE user_id = :user_id 
                AND is_active = 1
                AND next_due_date > :today
                AND next_due_date <= :end_of_month
        ');
        $stmt->execute([
            'user_id' => $userId,
            'today' => $today->format('Y-m-d'),
            'end_of_month' => $endOfMonth->format('Y-m-d')
        ]);
        $upcomingSubscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $subscriptionsByDay = [];
        foreach ($upcomingSubscriptions as $sub) {
            $day = intval(date('j', strtotime($sub['next_due_date'])));
            if (!isset($subscriptionsByDay[$day])) {
                $subscriptionsByDay[$day] = 0;
            }
            $change = $sub['type'] === 'income' ? floatval($sub['amount']) : -floatval($sub['amount']);
            $subscriptionsByDay[$day] += $change;
        }
        
        for ($day = $currentDay + 1; $day <= $daysInMonth; $day++) {
            $dateStr = $startOfMonth->format('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT);
            
            // Appliquer les abonnements du jour
            if (isset($subscriptionsByDay[$day])) {
                $projectedBalance += $subscriptionsByDay[$day];
            }
            
            // Soustraire l'estimation variable quotidienne
            $projectedBalance -= $dailyVariableEstimate;
            
            $balanceHistory[] = [
                'date' => $dateStr,
                'balance' => round($projectedBalance, 2),
                'type' => 'projected'
            ];
        }
        
        $this->json([
            'code' => 200,
            'current_global_balance' => round($currentGlobalBalance, 2),
            'days_remaining' => $daysRemaining,
            'projected_income' => round($projectedIncome, 2),
            'projected_fixed_costs' => round($projectedFixedCosts, 2),
            'projected_variable_spend' => round($projectedVariableSpend, 2),
            'estimated_end_balance' => round($estimatedEndBalance, 2),
            'status' => $status,
            'budget_details' => $budgetDetails,
            'balance_history' => $balanceHistory,
            'month' => $today->format('Y-m')
        ]);
    }

    /**
     * Résumé rapide pour le dashboard
     */
    #[CRoute('/api/forecast/summary', CHTTPMethod::GET, middleware: ['auth'])]
    public function summary(): void
    {
        $userId = $_SESSION['user']['id'];
        $today = new DateTime();
        
        $conn = Model::getConnection();
        
        // Net worth
        $stmt = $conn->prepare('SELECT SUM(current_balance) FROM Account WHERE user_id = :user_id AND include_in_net_worth = 1');
        $stmt->execute(['user_id' => $userId]);
        $netWorth = floatval($stmt->fetchColumn() ?: 0);
        
        // Revenus et dépenses du mois
        $stmt = $conn->prepare('
            SELECT type, SUM(amount) as total
            FROM Transaction 
            WHERE user_id = :user_id 
                AND type IN ("income", "expense")
                AND DATE_FORMAT(date, "%Y-%m") = :month
            GROUP BY type
        ');
        $stmt->execute(['user_id' => $userId, 'month' => $today->format('Y-m')]);
        $monthStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $monthIncome = floatval($monthStats['income'] ?? 0);
        $monthExpense = floatval($monthStats['expense'] ?? 0);
        
        // Abonnements à venir (7 jours)
        $stmt = $conn->prepare('
            SELECT COUNT(*) FROM Subscription 
            WHERE user_id = :user_id 
                AND is_active = 1
                AND next_due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ');
        $stmt->execute(['user_id' => $userId]);
        $upcomingCount = intval($stmt->fetchColumn());
        
        $this->json([
            'code' => 200,
            'net_worth' => round($netWorth, 2),
            'month_income' => round($monthIncome, 2),
            'month_expense' => round($monthExpense, 2),
            'month_balance' => round($monthIncome - $monthExpense, 2),
            'upcoming_subscriptions' => $upcomingCount
        ]);
    }
}
