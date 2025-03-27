<?php
namespace Interfaces;

interface DatabaseInterface {
    public static function getInstance(): self;
    public function getConnection(): \mysqli;
    public function prepare(string $sql): \mysqli_stmt;
    public function query(string $sql): \mysqli_result;
    public function close(): void;
}