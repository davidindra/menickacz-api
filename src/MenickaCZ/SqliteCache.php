<?php
namespace MenickaCZ;

class SqliteCache implements ICache
{
    private $sqlite;

    public function __construct(string $dbPath)
    {
        if(file_exists($dbPath)){
            $this->sqlite = new \SQLite3($dbPath);
        }else{
            $this->sqlite = new \SQLite3($dbPath);
            $this->sqlite->querySingle('
              CREATE TABLE cache
              (
                  key TEXT PRIMARY KEY NOT NULL,
                  value TEXT NOT NULL,
                  timestamp TIMESTAMP NOT NULL
              );
              CREATE UNIQUE INDEX cache_key_uindex ON cache (key);
            ');
        }
    }

    public function cache(string $key, callable $valueCallback, array $callbackParameters, \DateInterval $validity)
    {
        if($row = $this->sqlite->query(
            'SELECT * FROM cache WHERE "key" = "' . $key . '";'
        )->fetchArray(SQLITE3_ASSOC)){
            if(
                (new \DateTime())->setTimestamp($row['timestamp'])->add($validity)
                    ->getTimestamp()
                < time()){ // expired
                if(count($callbackParameters) > 0){
                    $value = call_user_func_array($valueCallback, $callbackParameters);
                }else{
                    $value = call_user_func($valueCallback);
                }

                $stmt = $this->sqlite->prepare(
                    'UPDATE cache SET "value" = :value, "timestamp" = ' . time() . ' WHERE "key" = :key;'
                );
                $stmt->bindValue(':key', $key);
                $stmt->bindValue(':value', serialize($value));
                $stmt->execute();

                return $value;
            }else{
                return unserialize($row['value']);
            }
        }else{
            if(count($callbackParameters) > 0){
                $value = call_user_func_array($valueCallback, $callbackParameters);
            }else{
                $value = call_user_func($valueCallback);
            }

            $stmt = $this->sqlite->prepare(
                'INSERT INTO cache ("key", "value", "timestamp") VALUES (:key, :value, ' . time() . ');'
            );
            $stmt->bindValue(':key', $key);
            $stmt->bindValue(':value', serialize($value));
            $stmt->execute();

            return $value;
        }
        if(count($callbackParameters) > 0){
            return call_user_func_array($valueCallback, $callbackParameters);
        }else{
            return call_user_func($valueCallback);
        }
    }
}