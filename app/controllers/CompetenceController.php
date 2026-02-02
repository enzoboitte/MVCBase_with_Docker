<?php

#[CMiddleware(middleware: ['auth'])]
class CompetenceController extends Controller
{
    // ==================== CATEGORIES ====================
    
    #[CPublic]
    #[CRoute('/competence/category', CHTTPMethod::GET)]
    public function indexCategories(): void
    {
        $l_cCon = Model::getConnection();
        $l_sSql = 'SELECT * FROM CompetenceCategory ORDER BY id ASC';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->execute();

        $l_aData = [];
        foreach ($l_oStmt->fetchAll(PDO::FETCH_ASSOC) as $l_aRow) 
        {
            $l_aData[] = [
                'id' => $l_aRow['id'],
                'name' => $l_aRow['name'],
                'description' => $l_aRow['description'],
                'icon' => $l_aRow['icon']
            ];
        }

        $this->json(["code" => (empty($l_aData) ? 404 : 200), "data" => $l_aData]);
    }

    #[CPublic]
    #[CRoute('/competence/category/{id}', CHTTPMethod::GET)]
    public function showCategory(string $id): void
    {
        $l_cCon = Model::getConnection();
        $l_sSql = 'SELECT * FROM CompetenceCategory WHERE id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmt->execute();

        $l_aRow = $l_oStmt->fetch(PDO::FETCH_ASSOC);
        if ($l_aRow) {
            $this->json(["code" => 200, "data" => $l_aRow]);
        } else {
            $this->json(["code" => 404, "message" => "Category not found"]);
        }
    }

    #[CRoute('/competence/category', CHTTPMethod::POST)]
    public function createCategory(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $l_cCon = Model::getConnection();
        
        $l_sSql = 'INSERT INTO CompetenceCategory (name, description, icon) VALUES (:name, :description, :icon)';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':name', $input['name']);
        $l_oStmt->bindParam(':description', $input['description']);
        $l_oStmt->bindParam(':icon', $input['icon']);
        $l_oStmt->execute();
        $this->json(["code" => 201, "message" => "Category created"]);
    }

    #[CRoute('/competence/category/{id}', CHTTPMethod::PUT)]
    public function updateCategory(string $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $l_cCon = Model::getConnection();
        
        $l_sSql = 'UPDATE CompetenceCategory SET name = :name, description = :description, icon = :icon WHERE id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':name', $input['name']);
        $l_oStmt->bindParam(':description', $input['description']);
        $l_oStmt->bindParam(':icon', $input['icon']);
        $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmt->execute();
        $this->json(["code" => 200, "message" => "Category updated"]);
    }

    #[CRoute('/competence/category/{id}', CHTTPMethod::DELETE)]
    public function deleteCategory(string $id): void
    {
        $l_cCon = Model::getConnection();
        $l_sSql = 'DELETE FROM CompetenceCategory WHERE id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmt->execute();
        $this->json(["code" => 200, "message" => "Category deleted"]);
    }

    // ==================== COMPETENCES ====================

    #[CPublic]
    #[CRoute('/competence', CHTTPMethod::GET)]
    public function index(): void
    {
        $l_cCon = Model::getConnection();
        $l_sSql = 'SELECT c.id, c.category_id, cat.name as category_name, t.code as techno_code, t.libelle, t.color 
                   FROM Competence c 
                   JOIN CompetenceCategory cat ON c.category_id = cat.id 
                   JOIN TechnoProject t ON c.techno_code = t.code 
                   ORDER BY cat.id, t.libelle ASC';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->execute();

        $l_aData = [];
        foreach ($l_oStmt->fetchAll(PDO::FETCH_ASSOC) as $l_aRow) 
        {
            $l_aData[] = [
                'id' => $l_aRow['id'],
                'category' => $l_aRow['category_name'],
                'techno' => $l_aRow['libelle'],
                'color' => $l_aRow['color']
            ];
        }

        $this->json(["code" => (empty($l_aData) ? 404 : 200), "data" => $l_aData]);
    }

    #[CPublic]
    #[CRoute('/competence/grouped', CHTTPMethod::GET)]
    public function groupedByCategory(): void
    {
        $l_cCon = Model::getConnection();
        
        // Récupérer les catégories
        $l_sSqlCat = 'SELECT * FROM CompetenceCategory ORDER BY id ASC';
        $l_oStmtCat = $l_cCon->prepare($l_sSqlCat);
        $l_oStmtCat->execute();
        $categories = $l_oStmtCat->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer les compétences avec leurs technos
        $l_sSql = 'SELECT c.category_id, t.code, t.libelle, t.color 
                   FROM Competence c 
                   JOIN TechnoProject t ON c.techno_code = t.code 
                   ORDER BY t.libelle ASC';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->execute();
        $competences = $l_oStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Grouper par catégorie
        $l_aData = [];
        foreach ($categories as $cat) {
            $l_aData[] = [
                'id' => $cat['id'],
                'name' => $cat['name'],
                'description' => $cat['description'],
                'icon' => $cat['icon'],
                'skills' => array_values(array_filter($competences, fn($c) => $c['category_id'] == $cat['id']))
            ];
        }

        $this->json(["code" => 200, "data" => $l_aData]);
    }

    #[CPublic]
    #[CRoute('/competence/technos/available', CHTTPMethod::GET)]
    public function availableTechnos(): void
    {
        $l_cCon = Model::getConnection();
        $l_sSql = 'SELECT code, libelle FROM TechnoProject ORDER BY libelle ASC';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->execute();

        $l_aData = $l_oStmt->fetchAll(PDO::FETCH_ASSOC);
        $this->json(["code" => 200, "data" => $l_aData]);
    }

    #[CPublic]
    #[CRoute('/competence/{id}', CHTTPMethod::GET)]
    public function show(string $id): void
    {
        $l_cCon = Model::getConnection();
        $l_sSql = 'SELECT c.id, c.category_id, t.code as techno_code 
                   FROM Competence c 
                   JOIN TechnoProject t ON c.techno_code = t.code 
                   WHERE c.id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmt->execute();

        $l_aRow = $l_oStmt->fetch(PDO::FETCH_ASSOC);
        if ($l_aRow) {
            $this->json(["code" => 200, "data" => [
                'category_id' => $l_aRow['category_id'],
                'techno_code' => $l_aRow['techno_code']
            ]]);
        } else {
            $this->json(["code" => 404, "message" => "Competence not found"]);
        }
    }

    #[CRoute('/competence', CHTTPMethod::POST)]
    public function create(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $l_cCon = Model::getConnection();
        
        $l_sSql = 'INSERT INTO Competence (category_id, techno_code) VALUES (:category_id, :techno_code)';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':category_id', $input['category_id']);
        $l_oStmt->bindParam(':techno_code', $input['techno_code']);
        $l_oStmt->execute();
        $this->json(["code" => 201, "message" => "Competence created"]);
    }

    #[CRoute('/competence/{id}', CHTTPMethod::PUT)]
    public function update(string $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $l_cCon = Model::getConnection();
        
        $l_sSql = 'UPDATE Competence SET category_id = :category_id, techno_code = :techno_code WHERE id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':category_id', $input['category_id']);
        $l_oStmt->bindParam(':techno_code', $input['techno_code']);
        $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmt->execute();
        $this->json(["code" => 200, "message" => "Competence updated"]);
    }

    #[CRoute('/competence/{id}', CHTTPMethod::DELETE)]
    public function delete(string $id): void
    {
        $l_cCon = Model::getConnection();
        $l_sSql = 'DELETE FROM Competence WHERE id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmt->execute();
        $this->json(["code" => 200, "message" => "Competence deleted"]);
    }
}
