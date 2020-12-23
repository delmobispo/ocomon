<?php

namespace includes\classes;

/**
 * Class ConnectPDO
 * 
 * @package includes\classes
 */
class ConnectPDO
{
    /** @const array */
    private const OPTIONS = [
        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, /* FETCH_OBJ */
        \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
        \PDO::ATTR_EMULATE_PREPARES => true,
    ];

    /** @var \PDO */
    private static $instance;

    /**
     * @return null|\PDO
     */
    public static function getInstance(): ?\PDO
    {
        if (empty(self::$instance)) {
            try {
                self::$instance = new \PDO(
                    "mysql:host=" . SQL_SERVER . ";dbname=" . SQL_DB,
                    SQL_USER,
                    SQL_PASSWD,
                    self::OPTIONS
                );
            } catch (\PDOException $exception) {
                //redirect("/problemas");
                //montar uma página de erro de conexão.
                echo 'Connection failed: ' . $exception->getMessage();
            }
        }

        return self::$instance;
    }

    /**
     * ConnectPDO constructor.
     */
    final private function __construct()
    {
    }

    /**
     * ConnectPDO clone.
     */
    // final private function __clone()
    // {
    // }
}