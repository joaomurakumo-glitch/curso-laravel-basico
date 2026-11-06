<?php

// Informa que este arquivo pertence à área "Feature" dos testes do projeto.
// Uma feature test verifica o aplicativo fazendo uma requisição, como se alguém abrisse uma página.

namespace Tests\Feature;

// Importa a ferramenta do Laravel que limpa e prepara o banco antes dos testes.
use Illuminate\Foundation\Testing\RefreshDatabase;
// Importa a classe de teste do projeto, que já conhece as ferramentas de teste do Laravel.
use Tests\TestCase;

// class cria uma classe: uma estrutura que agrupa dados e funções relacionadas.
// ExampleTest é o nome desta classe; o sufixo "Test" ajuda o PHPUnit a encontrá-la.
// extends significa "herda": esta classe recebe os recursos de teste de Tests\TestCase.
class ExampleTest extends TestCase
{
    // use, dentro da classe, ativa um trait: um pacote de funcionalidades que pode ser reutilizado.
    // RefreshDatabase prepara o banco de teste e desfaz as alterações depois, para os testes não se misturarem.
    use RefreshDatabase;

    // Este comentário está no formato PHPDoc: descreve o código e também pode ser lido por ferramentas.
    /**
     * Exemplo básico de teste de uma página.
     */

    // public significa que o PHPUnit pode acessar e executar este método.
    // O nome começando com "test_" segue a convenção que permite ao PHPUnit reconhecê-lo como teste.
    // : void informa que o método não devolve um valor para quem o chamou.
    public function test_the_application_returns_a_successful_response(): void
    {
        // $response é uma variável: guarda o resultado da requisição para verificarmos depois.
        // $this representa este próprio teste e -> chama uma funcionalidade disponível nele.
        // get('/') simula uma visita usando GET à página inicial, cujo endereço é "/".
        $response = $this->get('/');

        // -> chama uma verificação do resultado: o teste passa se a página responder com o código HTTP 200.
        // HTTP é a comunicação entre navegador e servidor; 200 significa que deu certo.
        // O ponto e vírgula (;) termina esta instrução em PHP.
        $response->assertStatus(200);
    }
}
