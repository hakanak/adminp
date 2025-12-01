<?php
// Dosya: /inc/db.php
// PDO Veritabanı Singleton Wrapper

class Database {
    private static $instance = null;
    private $pdo;

    /**
     * Singleton pattern - tek bir veritabanı bağlantısı
     */
    private function __construct() {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Veritabanı bağlantı hatası: ' . $e->getMessage());
        }
    }

    /**
     * Instance al
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * PDO nesnesini döndür
     */
    public function getPdo() {
        return $this->pdo;
    }

    /**
     * Sorgu çalıştır (SELECT)
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('SQL Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Tek satır getir
     * @param string $sql
     * @param array $params
     * @return array|false
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Tüm satırları getir
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * INSERT sorgusu
     * @param string $table
     * @param array $data
     * @return int Last insert ID
     */
    public function insert($table, $data) {
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        $this->query($sql, $values);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * UPDATE sorgusu
     * @param string $table
     * @param array $data
     * @param string $where
     * @param array $whereParams
     * @return int Affected rows
     */
    public function update($table, $data, $where, $whereParams = []) {
        $fields = [];
        foreach (array_keys($data) as $field) {
            $fields[] = "$field = ?";
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $fields),
            $where
        );

        $params = array_merge(array_values($data), $whereParams);
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * DELETE sorgusu
     * @param string $table
     * @param string $where
     * @param array $whereParams
     * @return int Affected rows
     */
    public function delete($table, $where, $whereParams = []) {
        $sql = sprintf('DELETE FROM %s WHERE %s', $table, $where);
        $stmt = $this->query($sql, $whereParams);
        return $stmt->rowCount();
    }

    /**
     * Kayıt sayısı
     * @param string $table
     * @param string $where
     * @param array $params
     * @return int
     */
    public function count($table, $where = '1=1', $params = []) {
        $sql = "SELECT COUNT(*) FROM $table WHERE $where";
        $stmt = $this->query($sql, $params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Transaction başlat
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Transaction commit
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Transaction rollback
     */
    public function rollback() {
        return $this->pdo->rollback();
    }

    /**
     * Son eklenen ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Clone ve unserialize engelle (singleton)
     */
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Kısa yoldan veritabanı instance al
 */
function db() {
    return Database::getInstance();
}
