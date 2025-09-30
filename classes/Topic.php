<?php
// Topic model for Assuntos
class Topic
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    public function getAll($subject_id = null)
    {
        if ($subject_id) {
            $stmt = $this->conn->prepare(
                "SELECT t.*, s.name AS subject_name
                 FROM topics t
                 JOIN subjects s ON t.subject_id = s.id
                 WHERE t.subject_id = ?
                 ORDER BY t.name"
            );
            $stmt->bind_param('i', $subject_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $query = 
                "SELECT t.*, s.name AS subject_name
                 FROM topics t
                 JOIN subjects s ON t.subject_id = s.id
                 ORDER BY s.name, t.name";
            $result = $this->conn->query($query);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM topics WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function create($subject_id, $name)
    {
        $stmt = $this->conn->prepare("INSERT INTO topics (subject_id, name) VALUES (?, ?)");
        $stmt->bind_param('is', $subject_id, $name);
        return $stmt->execute();
    }

    public function update($id, $subject_id, $name)
    {
        $stmt = $this->conn->prepare("UPDATE topics SET subject_id = ?, name = ? WHERE id = ?");
        $stmt->bind_param('isi', $subject_id, $name, $id);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM topics WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
