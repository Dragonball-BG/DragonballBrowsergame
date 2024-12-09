<?php
require_once 'models/BaseModel.php';

class BetaKeyModel extends BaseModel {
    /**
     * Check if a betakey is valid and not used.
     *
     * @param string $betakey
     * @return array|null Betakey data or null if invalid.
     */
    public function isValidBetakey($betakey) {
        $query = "SELECT * FROM betakey WHERE key_value = ? AND is_used = 0";
        return $this->fetchOne($query, [$betakey]);
    }

    /**
     * Mark a betakey as used.
     *
     * @param int $keyId
     * @param int $userId
     * @return bool True on success, false on failure.
     */
    public function markBetakeyAsUsed($keyId, $userId) {
        $query = "UPDATE betakey SET is_used = 1, used_by = ?, used_at = NOW() WHERE key_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $userId, $keyId);
        return $stmt->execute();
    }
}
