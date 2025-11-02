<?php
declare(strict_types=1);

final class MemoryStore {
  public function __construct(private PDO $db) {}

  public function upsert(
    string $scope, ?int $userId, ?int $conversationId, ?string $project,
    string $key, array $value, string $source='assistant', int $confidence=80
  ): void {
    $sql = "INSERT INTO ai_memory (scope,user_id,conversation_id,project,mkey,mval,source,confidence)
            VALUES (?,?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE mval=VALUES(mval), confidence=GREATEST(confidence, VALUES(confidence)), updated_at=NOW()";
    $this->db->prepare($sql)->execute([
      $scope, $userId, $conversationId, $project, $key,
      json_encode($value, JSON_UNESCAPED_UNICODE), $source, $confidence
    ]);
  }

  public function retrieveForUser(?int $userId, int $limit=12): array {
    if (!$userId) return [];
    $q = "SELECT mkey, mval, confidence FROM ai_memory WHERE scope='user' AND user_id=? ORDER BY confidence DESC, updated_at DESC LIMIT ?";
    $st = $this->db->prepare($q);
    $st->bindValue(1, $userId, PDO::PARAM_INT);
    $st->bindValue(2, $limit, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
  }
}
