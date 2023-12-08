<?php

namespace App\Stellar\Soroban\Contract;

class WasmManager {

    public function __construct(
        private readonly string $wasmFile
    ){}

    public function getWamsCode(): string
    {
        if(!file_exists($this->wasmFile)) {
            throw new \RuntimeException('Wams file does not exists. Did you compile your contract');
        }

        return file_get_contents($this->wasmFile);
    }
}