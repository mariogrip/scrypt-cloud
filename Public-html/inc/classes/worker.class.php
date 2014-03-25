<?php
$defflip = (!cfip()) ? exit(header('HTTP/1.1 401 Unauthorized')) : 1;

class Worker extends Base {
  protected $table = 'pool_worker';

  public function updateWorkers($account_id, $data) {
    $this->debug->append("STA " . __METHOD__, 4);
    if (!is_array($data)) {
      $this->setErrorMessage('No workers to update');
      return false;
    }
    $username = $this->user->getUserName($account_id);
    $iFailed = 0;
    foreach ($data as $key => $value) {
    if ('' === $value['username'] || '' === $value['password']) {
      $iFailed++;
    } else {
      // Check worker name first
      if (! preg_match("/^[0-9a-zA-Z_\-]*$/", $value['username'])) {
        $iFailed++;
        continue;
      }
      // Prefix the WebUser to Worker name
      $value['username'] = "$username." . $value['username'];
      $stmt = $this->mysqli->prepare("UPDATE $this->table SET password = ?, username = ?, monitor = ? WHERE account_id = ? AND id = ? LIMIT 1");
      if ( ! ( $this->checkStmt($stmt) && $stmt->bind_param('ssiii', $value['password'], $value['username'], $value['monitor'], $account_id, $key) && $stmt->execute()) )
        $iFailed++;
      }
    }
    if ($iFailed == 0)
      return true;
    return $this->sqlError('E0053', $iFailed);
  }

  public function getAllIdleWorkers($interval=600) {
    $this->debug->append("STA " . __METHOD__, 4);
    $stmt = $this->mysqli->prepare("
      SELECT w.account_id AS account_id, w.id AS id, w.username AS username
      FROM " . $this->share->getTableName() . " AS s
      RIGHT JOIN " . $this->getTableName() . " AS w
      ON w.username = s.username
      AND s.time > DATE_SUB(now(), INTERVAL ? SECOND)
      AND our_result = 'Y'
      WHERE w.monitor = 1
      AND s.id IS NULL
    ");
    if ($this->checkStmt($stmt) && $stmt->bind_param('i', $interval) && $stmt->execute() && $result = $stmt->get_result())
      return $result->fetch_all(MYSQLI_ASSOC);
    return $this->sqlError('E0054');
  }

  public function getWorker($id, $interval=600) {
    $this->debug->append("STA " . __METHOD__, 4);
    $stmt = $this->mysqli->prepare("
       SELECT id, username, password, monitor,
       ( SELECT COUNT(id) FROM " . $this->share->getTableName() . " WHERE username = w.username AND time > DATE_SUB(now(), INTERVAL ? SECOND)) AS count_all,
       ( SELECT COUNT(id) FROM " . $this->share->getArchiveTableName() . " WHERE username = w.username AND time > DATE_SUB(now(), INTERVAL ? SECOND)) AS count_all_archive,
       (
         SELECT
          IFNULL(IF(our_result='Y', ROUND(SUM(IF(difficulty=0, pow(2, (" . $this->config['difficulty'] . " - 16)), difficulty)) * POW(2, " . $this->config['target_bits'] . ") / ? / 1000), 0), 0) AS hashrate
          FROM " . $this->share->getTableName() . "
          WHERE
            username = w.username
          AND time > DATE_SUB(now(), INTERVAL ? SECOND)
        ) + (
         SELECT
          IFNULL(IF(our_result='Y', ROUND(SUM(IF(difficulty=0, pow(2, (" . $this->config['difficulty'] . " - 16)), difficulty)) * POW(2, " . $this->config['target_bits'] . ") / ? / 1000), 0), 0) AS hashrate
          FROM " . $this->share->getArchiveTableName() . "
          WHERE
            username = w.username
          AND time > DATE_SUB(now(), INTERVAL ? SECOND)
       ) AS hashrate,
       (
         SELECT IFNULL(ROUND(SUM(IF(difficulty=0, pow(2, (" . $this->config['difficulty'] . " - 16)), difficulty)) / count_all, 2), 0)
         FROM " . $this->share->getTableName() . "
         WHERE username = w.username AND time > DATE_SUB(now(), INTERVAL ? SECOND)
       ) + (
         SELECT IFNULL(ROUND(SUM(IF(difficulty=0, pow(2, (" . $this->config['difficulty'] . " - 16)), difficulty)) / count_all_archive, 2), 0)
         FROM " . $this->share->getArchiveTableName() . "
         WHERE username = w.username AND time > DATE_SUB(now(), INTERVAL ? SECOND)
       ) AS difficulty
       FROM $this->table AS w
       WHERE id = ?
       ");
    if ($this->checkStmt($stmt) && $stmt->bind_param('iiiiiiiii',$interval, $interval, $interval, $interval, $interval, $interval, $interval, $interval, $id) && $stmt->execute() && $result = $stmt->get_result())
      return $result->fetch_assoc();
    return $this->sqlError('E0055');
  }

  public function getWorkers($account_id, $interval=600) {
    $this->debug->append("STA " . __METHOD__, 4);
    $stmt = $this->mysqli->prepare("
      SELECT id, username, password, monitor,
       ( SELECT COUNT(id) FROM " . $this->share->getTableName() . " WHERE username = w.username AND time > DATE_SUB(now(), INTERVAL ? SECOND)) AS count_all,
       ( SELECT COUNT(id) FROM " . $this->share->getArchiveTableName() . " WHERE username = w.username AND time > DATE_SUB(now(), INTERVAL ? SECOND)) AS count_all_archive,
       (
         SELECT
          IFNULL(IF(our_result='Y', ROUND(SUM(IF(difficulty=0, pow(2, (" . $this->config['difficulty'] . " - 16)), difficulty)) * POW(2, " . $this->config['target_bits'] . ") / ? / 1000), 0), 0) AS hashrate
          FROM " . $this->share->getTableName() . "
          WHERE
            username = w.username
          AND time > DATE_SUB(now(), INTERVAL ? SECOND)
      ) + (
        SELECT
          IFNULL(IF(our_result='Y', ROUND(SUM(IF(difficulty=0, pow(2, (" . $this->config['difficulty'] . " - 16)), difficulty)) * POW(2, " . $this->config['target_bits'] . ") / ? / 1000), 0), 0) AS hashrate
          FROM " . $this->share->getArchiveTableName() . "
          WHERE
            username = w.username
          AND time > DATE_SUB(now(), INTERVAL ? SECOND)
      ) AS hashrate,
      (
        SELECT IFNULL(ROUND(SUM(IF(difficulty=0, pow(2, (" . $this->config['difficulty'] . " - 16)), difficulty)) / count_all, 2), 0)
        FROM " . $this->share->getTableName() . "
        WHERE username = w.username AND time > DATE_SUB(now(), INTERVAL ? SECOND)
      ) + (
        SELECT IFNULL(ROUND(SUM(IF(difficulty=0, pow(2, (" . $this->config['difficulty'] . " - 16)), difficulty)) / count_all_archive, 2), 0)
        FROM " . $this->share->getArchiveTableName() . "
        WHERE username = w.username AND time > DATE_SUB(now(), INTERVAL ? SECOND)
      ) AS difficulty
      FROM $this->table AS w
      WHERE account_id = ?");
    if ($this->checkStmt($stmt) && $stmt->bind_param('iiiiiiiii', $interval, $interval, $interval, $interval, $interval, $interval, $interval, $interval, $account_id) && $stmt->execute() && $result = $stmt->get_result())
      return $result->fetch_all(MYSQLI_ASSOC);
    return $this->sqlError('E0056');
  }

  /**
   * Get all currently active workers in the past 2 minutes
   * @param none
   * @return data mixed int count if any workers are active, false otherwise
   **/
  public function getCountAllActiveWorkers($interval=120) {
    $this->debug->append("STA " . __METHOD__, 4);
    if ($data = $this->memcache->get(__FUNCTION__)) return $data;
    $stmt = $this->mysqli->prepare("
      SELECT COUNT(DISTINCT(username)) AS total
      FROM "  . $this->share->getTableName() . "
      WHERE our_result = 'Y'
      AND time > DATE_SUB(now(), INTERVAL ? SECOND)");
    if ($this->checkStmt($stmt) && $stmt->bind_param('i', $interval) && $stmt->execute() && $result = $stmt->get_result())
      return $this->memcache->setCache(__FUNCTION__, $result->fetch_object()->total);
    return $this->sqlError();
  }


$worker = new Worker();
$worker->setDebug($debug);
$worker->setMysql($mysqli);
$worker->setMemcache($memcache);
$worker->setShare($share);
$worker->setConfig($config);
$worker->setUser($user);
$worker->setErrorCodes($aErrorCodes);

?>
