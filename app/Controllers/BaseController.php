<?php
namespace App\Controllers;

/**
 * Controller base que outros controllers podem estender.
 * Contém métodos úteis, como o carregamento de views.
 */
abstract class BaseController
{

    /**
     * Carrega um arquivo de view, passando dados para ele.
     *
     * @param string $viewName O nome do arquivo da view (ex: 'auth/login').
     * @param array  $data     Um array associativo de dados para extrair como variáveis na view.
     */
    protected function view($viewName, $data = [])
    {
        // Transforma as chaves do array de dados em variáveis (ex: $data['pageTitle'] vira $pageTitle).
        extract($data);

        // Inclui o arquivo da view a partir da pasta Views.
        require_once __DIR__ . "/../Views/{$viewName}.php";
    }
}
