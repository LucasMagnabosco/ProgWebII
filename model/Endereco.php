<?php

class Endereco {
    private string $rua;
    private string $numero;
    private string $complemento;
    private string $bairro;
    private string $cep;
    private string $cidade;
    private string $estado;

    public function __construct(
        string $rua = "",
        string $numero = "",
        string $complemento = "",
        string $bairro = "",
        string $cep = "",
        string $cidade = "",
        string $estado = ""
    ) {
        $this->rua = $rua;
        $this->numero = $numero;
        $this->complemento = $complemento;
        $this->bairro = $bairro;
        $this->cep = $cep;
        $this->cidade = $cidade;
        $this->estado = $estado;
    }

    // Getters
    public function getRua(): string {return $this->rua;}

    public function getNumero(): string {return $this->numero;}

    public function getComplemento(): string {return $this->complemento;}

    public function getBairro(): string {return $this->bairro;}

    public function getCep(): string {return $this->cep;}

    public function getCidade(): string {return $this->cidade;}

    public function getEstado(): string {return $this->estado;}

    // Setters
    public function setRua(string $rua): void {$this->rua = $rua;}

    public function setNumero(string $numero): void {$this->numero = $numero;}

    public function setComplemento(string $complemento): void {$this->complemento = $complemento;}

    public function setBairro(string $bairro): void {$this->bairro = $bairro;}

    public function setCep(string $cep): void {$this->cep = $cep;}

    public function setCidade(string $cidade): void {$this->cidade = $cidade;}

    public function setEstado(string $estado): void { $this->estado = $estado;}
} 