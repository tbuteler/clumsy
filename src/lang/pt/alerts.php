<?php

return [

    'items'          => 'item|items',

    'count'          => '{0} Nenhum item encontrado|{1}:count item encontrado|[2,Inf]:count itens encontrados',

    'item_added'     => 'Novo item adicionado com sucesso.',

    'item_updated'   => 'O item foi actualizado com sucesso.',

    'item_deleted'   => 'O item foi removido.',

    'invalid'        => 'Por favor corrija os erros listados abaixo e tente novamente.',

    'unauthorized'   => 'Não tem permissões para executar esta ação.',

    'required_by'    => 'O item não pode ser removido pois outros recursos dependem dele. Remova os itens relacionados primeiro ou seus vínculos e tente novamente.',

    'delete-confirm' => 'Tem certeza que deseja remover este item?',

    'user'   => [

        'added'     => 'Novo utilizador adicionado com sucesso.',

        'updated'   => 'O utilizador foi actualizado com sucesso.',

        'deleted'   => 'O utilizador foi removido.',

        'delete-confirm' => 'Tem certeza que deseja remover este utilizador?',

        'suicide'   => 'Não é possível remover o seu próprio utilizador.',

        'forbidden' => 'Não tem permissões para gerenciar utilizadores.',
    ],

    'auth'   => [

        'validate' => [
            'email.required'    => 'O campo email é requerido.',
            'password.required' => 'O campo palavra-passe é requerido.',
        ],

        'failed'            => 'Credenciais inválidas. Tente novamente.',

        'lockout'           => 'Utilizador suspenso por repetidas tentativas de login sem sucesso. Tente novamente em :seconds segundos.',

        'logged-out'        => 'A sessão foi terminada.',

        'reset-email-sent'  => 'Instruções para redefinir sua palavra-passe foram enviadas para o seu email.',

        'password-changed'  => 'Sua palavra-passe foi alterada com sucesso.',

        'reset-error'       => 'Ocorreu um erro ao redefinir sua palavra-passe. Por favor entre em contacto com o administrador do sistema.',

        'unknown-user'      => 'Não existe um utilizador com o e-mail fornecido.',
    ],

    'import' => [

        'required'  => 'Para adicionar :resources por favor utilize o website principal e ative as rotinas de actualização automática.',

        'success'   => 'A importação de :resources foi realizada com sucesso.',

        'fail'      => 'A importação falhou. Verifique a fonte e tente novamente.',

        'undefined' => 'A importação falhou. Não foi encontrado um importador para :resources',
    ],

    'email-error'    => 'Ocorreu um erro ao enviar o seu email. Por favor tente novamente mais tarde.',

    'token_mismatch' => 'Sua sessão expirou antes que pudesse salvar as mudanças. Se acredita que isto é um erro, por favor entre em contacto com o administrador do sistema.',

    'reorder' => [
        'success' => 'Ordenação guardada com sucesso.',
    ],

];
