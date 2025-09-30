<?php

class Flashcard
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    public function create($userId, $subjectId, $topicId, $question, $answer)
    {
        $stmt = $this->conn->prepare('INSERT INTO flashcards (user_id, subject_id, topic_id, question, answer) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('iiiss', $userId, $subjectId, $topicId, $question, $answer);
        return $stmt->execute();
    }

    public function getAllByUser($userId)
    {
        $stmt = $this->conn->prepare(
            'SELECT f.id, f.question, f.answer, f.subject_id, f.topic_id, s.name AS subject, t.name AS topic
             FROM flashcards f
             JOIN subjects s ON f.subject_id = s.id
             JOIN topics t ON f.topic_id = t.id
             WHERE f.user_id = ? ORDER BY f.id DESC'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare(
            'SELECT f.id, f.question, f.answer, f.subject_id, f.topic_id, s.name AS subject, t.name AS topic
             FROM flashcards f
             JOIN subjects s ON f.subject_id = s.id
             JOIN topics t ON f.topic_id = t.id
             WHERE f.id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function update($id, $subjectId, $topicId, $question, $answer)
    {
        $stmt = $this->conn->prepare('UPDATE flashcards SET subject_id = ?, topic_id = ?, question = ?, answer = ? WHERE id = ?');
        $stmt->bind_param('iissi', $subjectId, $topicId, $question, $answer, $id);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare('DELETE FROM flashcards WHERE id = ?');
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function logQuizAttempt($userId, $flashcardId, $isCorrect)
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO quiz_attempts (user_id, flashcard_id, is_correct) VALUES (?, ?, ?)'
        );
        $stmt->bind_param('iii', $userId, $flashcardId, $isCorrect);
        return $stmt->execute();
    }
}
