<?php

namespace App\Stellar\Soroban\Contract;

class WasmManager {

    public function __construct(
        private readonly string $wasmFile,
        private readonly string $wasmTokenFile
    ){}

    public function getWamsCode(): string
    {
        if(!file_exists($this->wasmFile)) {
            throw new \RuntimeException('Wasm file does not exists. Did you compile your contract');
        }

        return file_get_contents($this->wasmFile);
    }

    public function getTokenCode(): string
    {
        if(!file_exists($this->wasmTokenFile)) {
            throw new \RuntimeException('Wasm file does not exists. Did you compile your contract');
        }

        return file_get_contents($this->wasmTokenFile);
    }
}