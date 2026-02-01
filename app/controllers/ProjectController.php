<?php

class ProjectController extends Controller
{
    /**
     * Liste tous les projets (format simple pour table dashboard)
     */
    #[CRoute('/project', CHTTPMethod::GET)]
    public function index(): void
    {
        $l_cCon = Model::getConnection();
        
        // Récupérer tous les projets
        $l_sSql = 'SELECT id, name, description, link FROM Project ORDER BY id DESC';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->execute();

        $l_aData = [];
        foreach ($l_oStmt->fetchAll(PDO::FETCH_ASSOC) as $l_aRow) 
        {
            $l_aData[] = [
                'id' => $l_aRow['id'],
                'name' => $l_aRow['name'],
                'description' => mb_substr($l_aRow['description'], 0, 80) . (mb_strlen($l_aRow['description']) > 80 ? '...' : ''),
                'link' => $l_aRow['link'] ?? '-'
            ];
        }

        $this->json(["code" => (empty($l_aData) ? 404 : 200), "data" => $l_aData]);
    }

    /**
     * Liste tous les projets avec détails complets (pour la page home)
     */
    #[CRoute('/project/full', CHTTPMethod::GET)]
    public function indexFull(): void
    {
        $l_cCon = Model::getConnection();
        
        // Récupérer tous les projets
        $l_sSql = 'SELECT p.*, 
                   (SELECT ip.image_path FROM ImageProject ip WHERE ip.project_id = p.id ORDER BY ip.id LIMIT 1) as image
                   FROM Project p ORDER BY p.id DESC';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->execute();
        $projects = $l_oStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer les technologies pour chaque projet
        $l_aData = [];
        foreach ($projects as $project) {
            $l_sSqlTech = 'SELECT t.code, t.libelle, t.color 
                          FROM HaveProject hp 
                          JOIN TechnoProject t ON hp.techno_code = t.code 
                          WHERE hp.project_id = :project_id';
            $l_oStmtTech = $l_cCon->prepare($l_sSqlTech);
            $l_oStmtTech->bindParam(':project_id', $project['id'], PDO::PARAM_INT);
            $l_oStmtTech->execute();
            $technos = $l_oStmtTech->fetchAll(PDO::FETCH_ASSOC);
            
            // Récupérer toutes les images du projet
            $l_sSqlImg = 'SELECT id, image_path FROM ImageProject WHERE project_id = :project_id ORDER BY id';
            $l_oStmtImg = $l_cCon->prepare($l_sSqlImg);
            $l_oStmtImg->bindParam(':project_id', $project['id'], PDO::PARAM_INT);
            $l_oStmtImg->execute();
            $images = $l_oStmtImg->fetchAll(PDO::FETCH_ASSOC);
            
            $l_aData[] = [
                'id' => $project['id'],
                'name' => $project['name'],
                'description' => $project['description'],
                'link' => $project['link'],
                'image' => $project['image'],
                'images' => $images,
                'technologies' => $technos
            ];
        }

        $this->json(["code" => (empty($l_aData) ? 404 : 200), "data" => $l_aData]);
    }

    /**
     * Afficher un projet spécifique
     */
    #[CRoute('/project/{id}', CHTTPMethod::GET)]
    public function show(string $id): void
    {
        $l_cCon = Model::getConnection();
        
        $l_sSql = 'SELECT * FROM Project WHERE id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmt->execute();
        
        $project = $l_oStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            $this->json(["code" => 404, "message" => "Project not found"]);
            return;
        }
        
        // Récupérer les technologies
        $l_sSqlTech = 'SELECT t.code, t.libelle, t.color 
                      FROM HaveProject hp 
                      JOIN TechnoProject t ON hp.techno_code = t.code 
                      WHERE hp.project_id = :project_id';
        $l_oStmtTech = $l_cCon->prepare($l_sSqlTech);
        $l_oStmtTech->bindParam(':project_id', $id, PDO::PARAM_INT);
        $l_oStmtTech->execute();
        $technos = $l_oStmtTech->fetchAll(PDO::FETCH_ASSOC);
        
        // Récupérer les images
        $l_sSqlImg = 'SELECT id, image_path FROM ImageProject WHERE project_id = :project_id ORDER BY id';
        $l_oStmtImg = $l_cCon->prepare($l_sSqlImg);
        $l_oStmtImg->bindParam(':project_id', $id, PDO::PARAM_INT);
        $l_oStmtImg->execute();
        $images = $l_oStmtImg->fetchAll(PDO::FETCH_ASSOC);
        
        $l_aData = [
            'id' => $project['id'],
            'name' => $project['name'],
            'description' => $project['description'],
            'link' => $project['link'],
            'technologies' => $technos,
            'images' => $images
        ];

        $this->json(["code" => 200, "data" => $l_aData]);
    }

    /**
     * Créer un nouveau projet
     */
    #[CRoute('/project', CHTTPMethod::POST)]
    public function create(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $l_cCon = Model::getConnection();
        
        try {
            $l_cCon->beginTransaction();
            
            // Insérer le projet
            $l_sSql = 'INSERT INTO Project (name, description, link) VALUES (:name, :description, :link)';
            $l_oStmt = $l_cCon->prepare($l_sSql);
            $l_oStmt->bindParam(':name', $input['name']);
            $l_oStmt->bindParam(':description', $input['description']);
            $link = $input['link'] ?? null;
            $l_oStmt->bindParam(':link', $link);
            $l_oStmt->execute();
            
            $projectId = $l_cCon->lastInsertId();
            
            // Ajouter les technologies si présentes
            $technologies = $input['technologies'] ?? [];
            // Si c'est une string JSON, la décoder
            if (is_string($technologies)) {
                $technologies = json_decode($technologies, true) ?? [];
            }
            
            if (!empty($technologies)) {
                $l_sSqlTech = 'INSERT INTO HaveProject (project_id, techno_code) VALUES (:project_id, :techno_code)';
                $l_oStmtTech = $l_cCon->prepare($l_sSqlTech);
                
                foreach ($technologies as $technoCode) {
                    $l_oStmtTech->bindParam(':project_id', $projectId, PDO::PARAM_INT);
                    $l_oStmtTech->bindParam(':techno_code', $technoCode);
                    $l_oStmtTech->execute();
                }
            }
            
            $l_cCon->commit();
            $this->json(["code" => 201, "message" => "Project created", "id" => $projectId]);
            
        } catch (Exception $e) {
            $l_cCon->rollBack();
            $this->json(["code" => 500, "message" => "Error creating project: " . $e->getMessage()]);
        }
    }

    /**
     * Mettre à jour un projet
     */
    #[CRoute('/project/{id}', CHTTPMethod::PUT)]
    public function update(string $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $l_cCon = Model::getConnection();
        
        try {
            $l_cCon->beginTransaction();
            
            // Mettre à jour le projet
            $l_sSql = 'UPDATE Project SET name = :name, description = :description, link = :link WHERE id = :id';
            $l_oStmt = $l_cCon->prepare($l_sSql);
            $l_oStmt->bindParam(':name', $input['name']);
            $l_oStmt->bindParam(':description', $input['description']);
            $link = $input['link'] ?? null;
            $l_oStmt->bindParam(':link', $link);
            $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $l_oStmt->execute();
            
            // Supprimer les anciennes technologies et ajouter les nouvelles
            $l_sSqlDel = 'DELETE FROM HaveProject WHERE project_id = :project_id';
            $l_oStmtDel = $l_cCon->prepare($l_sSqlDel);
            $l_oStmtDel->bindParam(':project_id', $id, PDO::PARAM_INT);
            $l_oStmtDel->execute();
            
            $technologies = $input['technologies'] ?? [];
            // Si c'est une string JSON, la décoder
            if (is_string($technologies)) {
                $technologies = json_decode($technologies, true) ?? [];
            }
            
            if (!empty($technologies)) {
                $l_sSqlTech = 'INSERT INTO HaveProject (project_id, techno_code) VALUES (:project_id, :techno_code)';
                $l_oStmtTech = $l_cCon->prepare($l_sSqlTech);
                
                foreach ($technologies as $technoCode) {
                    $l_oStmtTech->bindParam(':project_id', $id, PDO::PARAM_INT);
                    $l_oStmtTech->bindParam(':techno_code', $technoCode);
                    $l_oStmtTech->execute();
                }
            }
            
            $l_cCon->commit();
            $this->json(["code" => 200, "message" => "Project updated"]);
            
        } catch (Exception $e) {
            $l_cCon->rollBack();
            $this->json(["code" => 500, "message" => "Error updating project: " . $e->getMessage()]);
        }
    }

    /**
     * Supprimer un projet
     */
    #[CRoute('/project/{id}', CHTTPMethod::DELETE)]
    public function delete(string $id): void
    {
        $l_cCon = Model::getConnection();
        
        // Les cascades s'occupent des HaveProject et ImageProject
        $l_sSql = 'DELETE FROM Project WHERE id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmt->execute();
        
        $this->json(["code" => 200, "message" => "Project deleted"]);
    }

    /**
     * Ajouter une image à un projet
     */
    #[CRoute('/project/{id}/image', CHTTPMethod::POST)]
    public function addImage(string $id): void
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $this->json(["code" => 400, "message" => "No image uploaded or upload error"]);
            return;
        }
        
        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            $this->json(["code" => 400, "message" => "Invalid image type"]);
            return;
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'project_' . $id . '_' . time() . '.' . $extension;
        $uploadPath = ROOT . '/public/uploads/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $l_cCon = Model::getConnection();
            $imagePath = '/public/uploads/' . $filename;
            
            $l_sSql = 'INSERT INTO ImageProject (project_id, image_path) VALUES (:project_id, :image_path)';
            $l_oStmt = $l_cCon->prepare($l_sSql);
            $l_oStmt->bindParam(':project_id', $id, PDO::PARAM_INT);
            $l_oStmt->bindParam(':image_path', $imagePath);
            $l_oStmt->execute();
            
            $this->json(["code" => 201, "message" => "Image uploaded", "path" => $imagePath]);
        } else {
            $this->json(["code" => 500, "message" => "Failed to save image"]);
        }
    }

    /**
     * Supprimer une image d'un projet
     */
    #[CRoute('/project/image/{imageId}', CHTTPMethod::DELETE)]
    public function deleteImage(string $imageId): void
    {
        $l_cCon = Model::getConnection();
        
        // Récupérer le chemin de l'image
        $l_sSql = 'SELECT image_path FROM ImageProject WHERE id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':id', $imageId, PDO::PARAM_INT);
        $l_oStmt->execute();
        $image = $l_oStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($image) {
            // Supprimer le fichier
            $filePath = ROOT . $image['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Supprimer de la BDD
            $l_sSqlDel = 'DELETE FROM ImageProject WHERE id = :id';
            $l_oStmtDel = $l_cCon->prepare($l_sSqlDel);
            $l_oStmtDel->bindParam(':id', $imageId, PDO::PARAM_INT);
            $l_oStmtDel->execute();
            
            $this->json(["code" => 200, "message" => "Image deleted"]);
        } else {
            $this->json(["code" => 404, "message" => "Image not found"]);
        }
    }
}
