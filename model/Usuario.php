<?php

class Usuario {
    private $id;
    private $nome;
    private $email;
    private $senha;
    private $telefone;
    private $endereco = null;
    private $tipo; // 'fornecedor' ou 'normal'
    private $cartaoCredito = null;
    private $is_admin = false;
 

    public function __construct(
        $nome,
        $email,
        $senha,
        $telefone,
        ?string $cartaoCredito = null,
        ?Endereco $endereco = null
    ) {
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->telefone = $telefone;
        $this->tipo = false;
        $this->cartaoCredito = $cartaoCredito;
        $this->endereco = $endereco;
        $this->is_admin = false;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getTelefone() {
        return $this->telefone;
    }

    public function getEndereco(): ?Endereco {
        return $this->endereco;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getCartaoCredito() {
        return $this->cartaoCredito;
    }

    public function getSenha() { return $this->senha; }

    public function isAdmin(): bool {
        return $this->is_admin;
    }

    // Setters
    public function setNome(string $nome) {
        $this->nome = $nome;
    }

    public function setEmail(string $email) {
        $this->email = $email;
    }

    public function setTelefone(string $telefone) {
        $this->telefone = $telefone;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }
    public function adicionarEndereco($endereco) {
        $this->endereco = $endereco;
        return $this;
    }

    public function setCartaoCredito(?string $cartaoCredito) {
        $this->cartaoCredito = $cartaoCredito;
    }

    public function setDescricao(?string $descricao) {
        $this->descricao = $descricao;
    }

    public function setEndereco($endereco) {
        $this->endereco = $endereco;
    }

    public function setIsAdmin(bool $is_admin) {
        $this->is_admin = $is_admin;
        return $this;
    }

    public function toJson() {
        $enderecoJson = null;
        if ($this->endereco !== null) {
            $enderecoJson = [
                'id' => $this->endereco->getId(),
                'rua' => $this->endereco->getRua(),
                'numero' => $this->endereco->getNumero(),
                'bairro' => $this->endereco->getBairro(),
                'cidade' => $this->endereco->getCidade(),
                'estado' => $this->endereco->getEstado(),
                'cep' => $this->endereco->getCep()
            ];
        }

        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'tipo' => $this->tipo,
            'cartao_credito' => $this->cartaoCredito,
            'is_admin' => $this->is_admin,
            'endereco' => $enderecoJson
        ];
    }
}
?>