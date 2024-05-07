<?php
class Database{
    public function __construct(private string $host, private string $user, private string $password, private string $name){

    }

    public function getConnection(): PDO{
        $dsn = "mysql:host={$this->host};dbname={$this->name};charset=UTF-8";

        return new PDO($dsn, $this->user, $this->password, [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]); 
    }
}
