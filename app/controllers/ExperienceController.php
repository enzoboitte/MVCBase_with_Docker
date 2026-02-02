<?php

class ExperienceController extends Controller
{
    /**
     * Liste toutes les expériences (format simple pour table dashboard)
     */
    #[CRoute('/experience', CHTTPMethod::GET)]
    public function index(): void
    {
        $l_cCon = Model::getConnection();
        
        $l_sSql = 'SELECT e.id, e.title, e.company, e.location, e.start_date, e.end_date, tc.contract_type
                   FROM Experience e
                   LEFT JOIN TypeContract tc ON e.contract_type_id = tc.id
                   ORDER BY e.end_date DESC, e.start_date DESC';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->execute();

        $l_aData = [];
        foreach ($l_oStmt->fetchAll(PDO::FETCH_ASSOC) as $l_aRow) 
        {
            // Récupérer les tâches
            $l_sSqlTasks = 'SELECT task_description FROM TaskExperience WHERE experience_id = :id ORDER BY id';
            $l_oStmtTasks = $l_cCon->prepare($l_sSqlTasks);
            $l_oStmtTasks->bindParam(':id', $l_aRow['id'], PDO::PARAM_INT);
            $l_oStmtTasks->execute();
            $tasks = array_column($l_oStmtTasks->fetchAll(PDO::FETCH_ASSOC), 'task_description');
            
            // Récupérer les technologies
            $l_sSqlTech = 'SELECT t.code, t.libelle, t.color 
                          FROM UseTechnoExperience ute 
                          JOIN TechnoProject t ON ute.techno_code = t.code 
                          WHERE ute.experience_id = :id';
            $l_oStmtTech = $l_cCon->prepare($l_sSqlTech);
            $l_oStmtTech->bindParam(':id', $l_aRow['id'], PDO::PARAM_INT);
            $l_oStmtTech->execute();
            $skills = array_column($l_oStmtTech->fetchAll(PDO::FETCH_ASSOC), 'libelle');
            
            $l_aData[] = [
                'id' => $l_aRow['id'],
                'title' => $l_aRow['title'],
                'company' => $l_aRow['company'],
                'location' => $l_aRow['location'],
                'type' => $l_aRow['contract_type'],
                'start_at' => $l_aRow['start_date'],
                'end_at' => $l_aRow['end_date'],
                'tasks' => $tasks,
                'skills' => $skills
            ];
        }

        $this->json(["code" => (empty($l_aData) ? 404 : 200), "data" => $l_aData]);
    }

    /**
     * Liste simple pour le tableau dashboard
     */
    #[CRoute('/experience/list', CHTTPMethod::GET)]
    public function list(): void
    {
        $l_cCon = Model::getConnection();
        
        $l_sSql = 'SELECT e.id, e.title, e.company, tc.contract_type, e.start_date, e.end_date
                   FROM Experience e
                   LEFT JOIN TypeContract tc ON e.contract_type_id = tc.id
                   ORDER BY e.end_date DESC, e.start_date DESC';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->execute();

        $l_aData = [];
        foreach ($l_oStmt->fetchAll(PDO::FETCH_ASSOC) as $l_aRow) 
        {
            $l_aData[] = [
                'id' => $l_aRow['id'],
                'title' => $l_aRow['title'],
                'company' => $l_aRow['company'],
                'type' => $l_aRow['contract_type'],
                'period' => $l_aRow['start_date'] . ' - ' . ($l_aRow['end_date'] ?? 'Présent')
            ];
        }

        $this->json(["code" => (empty($l_aData) ? 404 : 200), "data" => $l_aData]);
    }

    /**
     * Afficher une expérience spécifique
     */
    #[CRoute('/experience/{id}', CHTTPMethod::GET)]
    public function show(string $id): void
    {
        $l_cCon = Model::getConnection();
        
        $l_sSql = 'SELECT e.*, tc.contract_type
                   FROM Experience e
                   LEFT JOIN TypeContract tc ON e.contract_type_id = tc.id
                   WHERE e.id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmt->execute();

        $exp = $l_oStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$exp) {
            $this->json(["code" => 404, "message" => "Experience not found"]);
            return;
        }
        
        // Récupérer les tâches
        $l_sSqlTasks = 'SELECT id, task_description FROM TaskExperience WHERE experience_id = :id ORDER BY id';
        $l_oStmtTasks = $l_cCon->prepare($l_sSqlTasks);
        $l_oStmtTasks->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmtTasks->execute();
        $tasks = $l_oStmtTasks->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer les technologies
        $l_sSqlTech = 'SELECT t.code, t.libelle, t.color 
                      FROM UseTechnoExperience ute 
                      JOIN TechnoProject t ON ute.techno_code = t.code 
                      WHERE ute.experience_id = :id';
        $l_oStmtTech = $l_cCon->prepare($l_sSqlTech);
        $l_oStmtTech->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmtTech->execute();
        $technologies = $l_oStmtTech->fetchAll(PDO::FETCH_ASSOC);
        
        $l_aData = [
            'id' => $exp['id'],
            'title' => $exp['title'],
            'company' => $exp['company'],
            'location' => $exp['location'],
            'contract_type_id' => $exp['contract_type_id'],
            'contract_type' => $exp['contract_type'],
            'start_date' => $exp['start_date'],
            'end_date' => $exp['end_date'],
            'description' => $exp['description'],
            'tasks' => $tasks,
            'technologies' => $technologies
        ];

        $this->json(["code" => 200, "data" => $l_aData]);
    }

    /**
     * Créer une nouvelle expérience
     */
    #[CRoute('/experience', CHTTPMethod::POST)]
    public function create(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $l_cCon = Model::getConnection();
        
        try {
            $l_cCon->beginTransaction();
            
            // Insérer l'expérience
            $l_sSql = 'INSERT INTO Experience (title, company, location, contract_type_id, start_date, end_date, description) 
                       VALUES (:title, :company, :location, :contract_type_id, :start_date, :end_date, :description)';
            $l_oStmt = $l_cCon->prepare($l_sSql);
            $l_oStmt->bindParam(':title', $input['title']);
            $l_oStmt->bindParam(':company', $input['company']);
            $l_oStmt->bindParam(':location', $input['location']);
            $l_oStmt->bindParam(':contract_type_id', $input['contract_type_id'], PDO::PARAM_INT);
            $l_oStmt->bindParam(':start_date', $input['start_date']);
            $endDate = !empty($input['end_date']) ? $input['end_date'] : null;
            $l_oStmt->bindParam(':end_date', $endDate);
            $l_oStmt->bindParam(':description', $input['description']);
            $l_oStmt->execute();
            
            $expId = $l_cCon->lastInsertId();
            
            // Ajouter les technologies
            $technologies = $input['technologies'] ?? [];
            if (is_string($technologies)) {
                $technologies = json_decode($technologies, true) ?? [];
            }
            
            if (!empty($technologies)) {
                $l_sSqlTech = 'INSERT INTO UseTechnoExperience (experience_id, techno_code) VALUES (:exp_id, :techno_code)';
                $l_oStmtTech = $l_cCon->prepare($l_sSqlTech);
                
                foreach ($technologies as $technoCode) {
                    $l_oStmtTech->bindParam(':exp_id', $expId, PDO::PARAM_INT);
                    $l_oStmtTech->bindParam(':techno_code', $technoCode);
                    $l_oStmtTech->execute();
                }
            }
            
            // Ajouter les tâches
            $tasks = $input['tasks'] ?? [];
            if (is_string($tasks)) {
                $tasks = json_decode($tasks, true) ?? [];
            }
            
            if (!empty($tasks)) {
                $l_sSqlTask = 'INSERT INTO TaskExperience (experience_id, task_description) VALUES (:exp_id, :task)';
                $l_oStmtTask = $l_cCon->prepare($l_sSqlTask);
                
                foreach ($tasks as $task) {
                    if (!empty(trim($task))) {
                        $l_oStmtTask->bindParam(':exp_id', $expId, PDO::PARAM_INT);
                        $l_oStmtTask->bindParam(':task', $task);
                        $l_oStmtTask->execute();
                    }
                }
            }
            
            $l_cCon->commit();
            $this->json(["code" => 201, "message" => "Experience created", "id" => $expId]);
            
        } catch (Exception $e) {
            $l_cCon->rollBack();
            $this->json(["code" => 500, "message" => "Error creating experience: " . $e->getMessage()]);
        }
    }

    /**
     * Mettre à jour une expérience
     */
    #[CRoute('/experience/{id}', CHTTPMethod::PUT)]
    public function update(string $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $l_cCon = Model::getConnection();
        
        try {
            $l_cCon->beginTransaction();
            
            // Mettre à jour l'expérience
            $l_sSql = 'UPDATE Experience SET title = :title, company = :company, location = :location, 
                       contract_type_id = :contract_type_id, start_date = :start_date, end_date = :end_date, 
                       description = :description WHERE id = :id';
            $l_oStmt = $l_cCon->prepare($l_sSql);
            $l_oStmt->bindParam(':title', $input['title']);
            $l_oStmt->bindParam(':company', $input['company']);
            $l_oStmt->bindParam(':location', $input['location']);
            $l_oStmt->bindParam(':contract_type_id', $input['contract_type_id'], PDO::PARAM_INT);
            $l_oStmt->bindParam(':start_date', $input['start_date']);
            $endDate = !empty($input['end_date']) ? $input['end_date'] : null;
            $l_oStmt->bindParam(':end_date', $endDate);
            $l_oStmt->bindParam(':description', $input['description']);
            $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $l_oStmt->execute();
            
            // Supprimer et réinsérer les technologies
            $l_sSqlDel = 'DELETE FROM UseTechnoExperience WHERE experience_id = :id';
            $l_oStmtDel = $l_cCon->prepare($l_sSqlDel);
            $l_oStmtDel->bindParam(':id', $id, PDO::PARAM_INT);
            $l_oStmtDel->execute();
            
            $technologies = $input['technologies'] ?? [];
            if (is_string($technologies)) {
                $technologies = json_decode($technologies, true) ?? [];
            }
            
            if (!empty($technologies)) {
                $l_sSqlTech = 'INSERT INTO UseTechnoExperience (experience_id, techno_code) VALUES (:exp_id, :techno_code)';
                $l_oStmtTech = $l_cCon->prepare($l_sSqlTech);
                
                foreach ($technologies as $technoCode) {
                    $l_oStmtTech->bindParam(':exp_id', $id, PDO::PARAM_INT);
                    $l_oStmtTech->bindParam(':techno_code', $technoCode);
                    $l_oStmtTech->execute();
                }
            }
            
            // Supprimer et réinsérer les tâches
            $l_sSqlDelTask = 'DELETE FROM TaskExperience WHERE experience_id = :id';
            $l_oStmtDelTask = $l_cCon->prepare($l_sSqlDelTask);
            $l_oStmtDelTask->bindParam(':id', $id, PDO::PARAM_INT);
            $l_oStmtDelTask->execute();
            
            $tasks = $input['tasks'] ?? [];
            if (is_string($tasks)) {
                $tasks = json_decode($tasks, true) ?? [];
            }
            
            if (!empty($tasks)) {
                $l_sSqlTask = 'INSERT INTO TaskExperience (experience_id, task_description) VALUES (:exp_id, :task)';
                $l_oStmtTask = $l_cCon->prepare($l_sSqlTask);
                
                foreach ($tasks as $task) {
                    if (!empty(trim($task))) {
                        $l_oStmtTask->bindParam(':exp_id', $id, PDO::PARAM_INT);
                        $l_oStmtTask->bindParam(':task', $task);
                        $l_oStmtTask->execute();
                    }
                }
            }
            
            $l_cCon->commit();
            $this->json(["code" => 200, "message" => "Experience updated"]);
            
        } catch (Exception $e) {
            $l_cCon->rollBack();
            $this->json(["code" => 500, "message" => "Error updating experience: " . $e->getMessage()]);
        }
    }

    /**
     * Supprimer une expérience
     */
    #[CRoute('/experience/{id}', CHTTPMethod::DELETE)]
    public function delete(string $id): void
    {
        $l_cCon = Model::getConnection();
        
        // Les cascades s'occupent des tâches et technologies
        $l_sSql = 'DELETE FROM Experience WHERE id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmt->execute();
        
        $this->json(["code" => 200, "message" => "Experience deleted"]);
    }

    /**
     * Liste des types de contrat
     */
    #[CRoute('/contract-types', CHTTPMethod::GET)]
    public function contractTypes(): void
    {
        $l_cCon = Model::getConnection();
        
        $l_sSql = 'SELECT * FROM TypeContract ORDER BY id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->execute();
        
        $l_aData = $l_oStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->json(["code" => 200, "data" => $l_aData]);
    }
}
