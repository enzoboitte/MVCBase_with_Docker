<?php

class DiplomaController extends Controller
{
    #[CRoute('/diploma', CHTTPMethod::GET)]
    public function index(): void
    {
        $l_cCon = Model::getConnection();
        $l_sSql = 'SELECT * FROM Diploma ORDER BY end_date DESC, start_date DESC';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->execute();

        $l_aData = [];
        foreach ($l_oStmt->fetchAll(PDO::FETCH_ASSOC) as $l_aRow) 
        {
            $l_aData[] = [
                'id' => $l_aRow['id'],
                'name' => $l_aRow['name'],
                'description' => $l_aRow['description'],
                'school' => $l_aRow['school'],
                'country' => $l_aRow['country'],
                'start_at' => $l_aRow['start_date'],
                'end_at' => $l_aRow['end_date'] ?? 'Act.'
            ];
        }

        $this->json(["code" => (empty($l_aData) ? 404 : 200), "data" => $l_aData]);
    }

    #[CRoute('/diploma/{id}', CHTTPMethod::GET)]
    public function show(string $id): void
    {
        $l_cCon = Model::getConnection();
        $l_sSql = 'SELECT * FROM Diploma WHERE id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmt->execute();

        $l_aRow = $l_oStmt->fetch(PDO::FETCH_ASSOC);
        if ($l_aRow) {
            $l_aData = [
                'name' => $l_aRow['name'],
                'description' => $l_aRow['description'],
                'school' => $l_aRow['school'],
                'country' => $l_aRow['country'],
                'start_at' => $l_aRow['start_date'],
                'end_at' => $l_aRow['end_date'] ?? 'Act.'
            ];
            $this->json(["code" => 200, "data" => $l_aData]);
        } else {
            $this->json(["code" => 404, "message" => "Diploma not found"]);
        }
    }

    #[CRoute('/diploma', CHTTPMethod::POST)]
    public function create(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $l_cCon = Model::getConnection();
        $l_sSql = 'INSERT INTO Diploma (name, description, school, country, start_date, end_date) 
                   VALUES (:name, :description, :school, :country, :start_date, :end_date)';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':name', $input['name']);
        $l_oStmt->bindParam(':description', $input['description']);
        $l_oStmt->bindParam(':school', $input['school']);
        $l_oStmt->bindParam(':country', $input['country']);
        $l_oStmt->bindParam(':start_date', $input['start_at']);
        $l_oStmt->bindParam(':end_date', $input['end_at']);
        $l_oStmt->execute();
        $this->json(["code" => 201, "message" => "Diploma created"]);
    }

    #[CRoute('/diploma/{id}', CHTTPMethod::PUT)]
    public function update(string $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $l_cCon = Model::getConnection();
        $l_sSql = 'UPDATE Diploma SET name = :name, description = :description, school = :school, country = :country, 
                   start_date = :start_date, end_date = :end_date WHERE id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':name', $input['name']);
        $l_oStmt->bindParam(':description', $input['description']);
        $l_oStmt->bindParam(':school', $input['school']);
        $l_oStmt->bindParam(':country', $input['country']);
        $l_oStmt->bindParam(':start_date', $input['start_at']);
        $l_oStmt->bindParam(':end_date', $input['end_at']);
        $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmt->execute();
        $this->json(["code" => 200, "message" => "Diploma updated"]);
    }

    #[CRoute('/diploma/{id}', CHTTPMethod::DELETE)]
    public function delete(string $id): void
    {
        $l_cCon = Model::getConnection();
        $l_sSql = 'DELETE FROM Diploma WHERE id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmt->execute();
        $this->json(["code" => 200, "message" => "Diploma deleted"]);
    }
}