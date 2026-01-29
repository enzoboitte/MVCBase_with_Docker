<?php

class ContactController extends Controller
{
    #[CRoute('/contact', CHTTPMethod::GET)]
    public function index(): void
    {
        $where = '1=1';
        if(isset($_REQUEST['filter']))
        {
            $filter = explode(',', $_REQUEST['filter']);
            
        }
        $l_cCon = Model::getConnection();
        $l_sSql = 'SELECT * FROM ContactMessage WHERE ' . $where . ' ORDER BY created_at DESC';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->execute();
        $l_aData = [];
        foreach ($l_oStmt->fetchAll(PDO::FETCH_ASSOC) as $l_aRow) 
        {
            $l_aData[] = [
                'id' => $l_aRow['id'],
                'name' => $l_aRow['name'],
                'email' => $l_aRow['email'],
                'subject' => $l_aRow['subject'],
                'message' => $l_aRow['message'],
                'status' => $l_aRow['status'],
                'pin' => $l_aRow['pin'],
                'created_at' => $l_aRow['created_at']
            ];
        }

        $this->json(["code" => (empty($l_aData) ? 404 : 200), "data" => $l_aData]);
    }

    #[CRoute('/contact', CHTTPMethod::POST)]
    public function create(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $l_cCon = Model::getConnection();
        $l_sSql = 'INSERT INTO ContactMessage (name, email, subject, message) VALUES (:name, :email, :subject, :message)';

        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':name', $input['name'], PDO::PARAM_STR);
        $l_oStmt->bindParam(':email', $input['email'], PDO::PARAM_STR);
        $l_oStmt->bindParam(':subject', $input['subject'], PDO::PARAM_STR);
        $l_oStmt->bindParam(':message', $input['message'], PDO::PARAM_STR);
        $l_oStmt->execute();
        $this->json(["code" => 201, "message" => "Message created successfully"]);
    }

    #[CRoute('/contact/{id}', CHTTPMethod::DELETE)]
    public function delete(string $id): void
    {
        $l_cCon = Model::getConnection();
        $l_sSql = 'DELETE FROM ContactMessage WHERE id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmt->execute();
        $this->json(["code" => 200, "message" => "Message deleted successfully"]);
    }

    #[CRoute('/contact/{id}', CHTTPMethod::GET)]
    public function show(string $id): void
    {
        $l_cCon = Model::getConnection();
        $l_sSql = 'SELECT * FROM ContactMessage WHERE id = :id';
        $l_oStmt = $l_cCon->prepare($l_sSql);
        $l_oStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmt->execute();
        $l_aRow = $l_oStmt->fetch(PDO::FETCH_ASSOC);
        if ($l_aRow) {
            $l_aData = [
                'id' => $l_aRow['id'],
                'name' => $l_aRow['name'],
                'email' => $l_aRow['email'],
                'subject' => $l_aRow['subject'],
                'message' => $l_aRow['message'],
                'status' => $l_aRow['status'],
                'pin' => $l_aRow['pin'],
                'created_at' => $l_aRow['created_at']
            ];
            $this->json(["code" => 200, "data" => $l_aData]);
        } else {
            $this->json(["code" => 404, "message" => "Message not found"]);
        }
    }

    #[CRoute('/contact/status/{id}/favorite', CHTTPMethod::PUT)]
    public function toggleFavorite(string $id): void
    {
        $this->togglePin($id, 'favorite');
    }
    
    #[CRoute('/contact/status/{id}/archive', CHTTPMethod::PUT)]
    public function toggleArchive(string $id): void
    {
        $this->togglePin($id, 'archived');
    }

    private function togglePin(string $id, string $pin): void
    {
        $l_cCon = Model::getConnection();
        // First, get the current pin
        $l_sSqlSelect = 'SELECT pin FROM ContactMessage WHERE id = :id';
        $l_oStmtSelect = $l_cCon->prepare($l_sSqlSelect);
        $l_oStmtSelect->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmtSelect->execute();
        $l_aRow = $l_oStmtSelect->fetch(PDO::FETCH_ASSOC);

        if ($l_aRow) {
            $currentPin = $l_aRow['pin'];
            $newPin = ($currentPin === $pin) ? 'normal' : $pin;

            // Update to the new pin
            $l_sSqlUpdate = 'UPDATE ContactMessage SET pin = :pin WHERE id = :id';
            $l_oStmtUpdate = $l_cCon->prepare($l_sSqlUpdate);
            $l_oStmtUpdate->bindParam(':pin', $newPin, PDO::PARAM_STR);
            $l_oStmtUpdate->bindParam(':id', $id, PDO::PARAM_INT);
            $l_oStmtUpdate->execute();

            $this->json(["code" => 200, "message" => "Message archive pin toggled successfully"]);
        } else {
            $this->json(["code" => 404, "message" => "Message not found"]);
        }
    }

    #[CRoute('/contact/status/{id}/read', CHTTPMethod::PUT)]
    public function toggleStatus(string $id): void
    {
        $l_cCon = Model::getConnection();
        // First, get the current status
        $l_sSqlSelect = 'SELECT status FROM ContactMessage WHERE id = :id';
        $l_oStmtSelect = $l_cCon->prepare($l_sSqlSelect);
        $l_oStmtSelect->bindParam(':id', $id, PDO::PARAM_INT);
        $l_oStmtSelect->execute();
        $l_aRow = $l_oStmtSelect->fetch(PDO::FETCH_ASSOC);

        if ($l_aRow) {
            $currentStatus = $l_aRow['status'];
            $newStatus = ($currentStatus === 'read') ? 'new' : 'read';

            // Update to the new status
            $l_sSqlUpdate = 'UPDATE ContactMessage SET status = :status WHERE id = :id';
            $l_oStmtUpdate = $l_cCon->prepare($l_sSqlUpdate);
            $l_oStmtUpdate->bindParam(':status', $newStatus, PDO::PARAM_STR);
            $l_oStmtUpdate->bindParam(':id', $id, PDO::PARAM_INT);
            $l_oStmtUpdate->execute();

            $this->json(["code" => 200, "message" => "Message read status toggled successfully"]);
        } else {
            $this->json(["code" => 404, "message" => "Message not found"]);
        }
    }
}