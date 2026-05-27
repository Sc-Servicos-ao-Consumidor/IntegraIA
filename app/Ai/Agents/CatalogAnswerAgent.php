<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::OpenAI)]
#[Model('gpt-5.4-nano')]
#[Temperature(0.7)]
#[Timeout(120)]
class CatalogAnswerAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Você é um assistente especializado em catálogo de produtos. Responda perguntas somente com base no contexto do catálogo fornecido na mensagem do usuário. Não invente informações que não estejam no catálogo. Se a resposta não puder ser encontrada no catálogo fornecido, informe isso claramente.';
    }
}
