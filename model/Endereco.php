<?php

class Endereco {
    private $rua;
    private $numero;
    private $complemento;
    private $bairro;
    private $cep;
    private $cidade;
    private $estado;
    private $id;

    public function __construct(
        $rua = "",
        $numero = "",
        $complemento = "",
        $bairro = "",
        $cep = "",
        $cidade = "",
        $estado = ""
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
    public function getRua() {
        return $this->rua;
    }

    public function getNumero() {
        return $this->numero;
    }

    public function getComplemento() {
        return $this->complemento;
    }

    public function getBairro() {
        return $this->bairro;
    }

    public function getCep() {
        return $this->cep;
    }

    public function getCidade() {
        return $this->cidade;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getId() {
        return $this->id;
    }

    // Setters
    public function setRua($rua) {
        $this->rua = $rua;
    }

    public function setNumero($numero) {
        $this->numero = $numero;
    }

    public function setComplemento($complemento) {
        $this->complemento = $complemento;
    }

    public function setBairro($bairro) {
        $this->bairro = $bairro;
    }

    public function setCep($cep) {
        $this->cep = $cep;
    }

    public function setCidade($cidade) {
        $this->cidade = $cidade;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setId($id) {
        $this->id = $id;
    }
} 