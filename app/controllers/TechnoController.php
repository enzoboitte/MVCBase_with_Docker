<?php

class TechnoController extends Controller
{
    #[CRoute('/techno', CHTTPMethod::GET)]
    public function index(): void
    {
        $l_cCon = Model::getConnection();
        $l_sSql = 'SELECT * FROM TechnoProject ORDER BY libelle ASC';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->execute();

        $l_aData = [];
        foreach ($l_oStmt->fetchAll(PDO::FETCH_ASSOC) as $l_aRow) 
        {
            $l_aData[] = [
                'id' => $l_aRow['code'],
                'code' => $l_aRow['code'],
                'libelle' => $l_aRow['libelle'],
                'color' => $l_aRow['color']
            ];
        }

        $this->json(["code" => (empty($l_aData) ? 404 : 200), "data" => $l_aData]);
    }

    #[CRoute('/techno/{code}', CHTTPMethod::GET)]
    public function show(string $code): void
    {
        $l_cCon = Model::getConnection();
        $l_sSql = 'SELECT * FROM TechnoProject WHERE code = :code';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':code', $code, PDO::PARAM_STR);
        $l_oStmt->execute();

        $l_aRow = $l_oStmt->fetch(PDO::FETCH_ASSOC);
        if ($l_aRow) {
            $l_aData = [
                'code' => $l_aRow['code'],
                'libelle' => $l_aRow['libelle'],
                'color' => $l_aRow['color']
            ];
            $this->json(["code" => 200, "data" => $l_aData]);
        } else {
            $this->json(["code" => 404, "message" => "Technology not found"]);
        }
    }

    #[CRoute('/techno', CHTTPMethod::POST)]
    public function create(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $l_cCon = Model::getConnection();
        
        // Générer le code à partir du libellé si non fourni
        $code = $input['code'] ?? strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $input['libelle']));
        
        $l_sSql = 'INSERT INTO TechnoProject (code, libelle, color) VALUES (:code, :libelle, :color)';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':code', $code);
        $l_oStmt->bindParam(':libelle', $input['libelle']);
        $l_oStmt->bindParam(':color', $input['color']);
        $l_oStmt->execute();
        $this->json(["code" => 201, "message" => "Technology created"]);
    }

    #[CRoute('/techno/{code}', CHTTPMethod::PUT)]
    public function update(string $code): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $l_cCon = Model::getConnection();
        
        $l_sSql = 'UPDATE TechnoProject SET libelle = :libelle, color = :color WHERE code = :code';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':libelle', $input['libelle']);
        $l_oStmt->bindParam(':color', $input['color']);
        $l_oStmt->bindParam(':code', $code, PDO::PARAM_STR);
        $l_oStmt->execute();
        $this->json(["code" => 200, "message" => "Technology updated"]);
    }

    #[CRoute('/techno/{code}', CHTTPMethod::DELETE)]
    public function delete(string $code): void
    {
        $l_cCon = Model::getConnection();
        $l_sSql = 'DELETE FROM TechnoProject WHERE code = :code';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':code', $code, PDO::PARAM_STR);
        $l_oStmt->execute();
        $this->json(["code" => 200, "message" => "Technology deleted"]);
    }
}
