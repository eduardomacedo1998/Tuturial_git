<?php

class Subject
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    public function getAll()
    {
        $result = $this->conn->query('SELECT id, name FROM subjects ORDER BY name');
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare('SELECT id, name FROM subjects WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function create($name)
    {
        $stmt = $this->conn->prepare('INSERT INTO subjects (name) VALUES (?)');
        $stmt->bind_param('s', $name);
        return $stmt->execute();
    }

    public function update($id, $name)
    {
        $stmt = $this->conn->prepare('UPDATE subjects SET name = ? WHERE id = ?');
        $stmt->bind_param('si', $name, $id);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare('DELETE FROM subjects WHERE id = ?');
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}