<?php

/**
 * Exemple de modèle de base
 * À étendre selon tes besoins (connexion PDO, etc.)
 */
class Model
{
    protected static ?PDO $pdo = null;

    protected static function getDb(): PDO
    {
        if (self::$pdo === null) {
            // Utilise les variables d'environnement Docker ou valeurs par défaut
            $host = getenv('DB_HOST') ?: 'db';
            $port = getenv('DB_PORT') ?: '3306';
            $database = getenv('DB_DATABASE') ?: 'portfolio';
            $username = getenv('DB_USERNAME') ?: 'userenzo';
            $password = getenv('DB_PASSWORD') ?: '123456789';

            self::$pdo = new PDO(
                "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
        return self::$pdo;
    }

    public static function getConnection(): PDO
    {
        return self::getDb();
    }

    public static function F_vInitBDD()
    {
        /*$files = scandir(__DIR__ . '/../../sql');
        foreach ($files as $file) {
            echo $file . "<br>";
            if ($file != 'bdd.sql')
                continue;
            echo file_get_contents(__DIR__ . '/../../sql/' . $file) . "<br>".__DIR__ . '/../../sql/' . $file;
        }*/

        $sql = file_get_contents(__DIR__ . '/../../sql/bdd.sql');
        self::getDb()->exec($sql);
    }
}
