<?php

return array(

	'items'          => 'item|items',

	'count'          => '{0} Nenhum item encontrado|{1}:count item encontrado|[2,Inf]:count itens encontrados',

	'item_added'     => 'Novo item adicionado com sucesso.',

	'item_updated'   => 'O item foi actualizado com sucesso.',

	'item_deleted'   => 'O item foi removido.',

	'invalid'        => 'Por favor corrija os erros listados abaixo e tente novamente.',

	'required_by'    => 'O item não pode ser removido pois outros recursos dependem dele. Remova os itens relacionados primeiro ou seus vínculos e tente novamente.',

	'delete_confirm' => 'Tem certeza que deseja remover este item?',

	'user'   => array(

		'added'     => 'Novo utilizador adicionado com sucesso.',

		'updated'   => 'O utilizador foi actualizado com sucesso.',

		'deleted'   => 'O utilizador foi removido.',

		'delete_confirm' => 'Tem certeza que deseja remover este utilizador?',

		'suicide'   => 'Não é possível remover o seu próprio utilizador.',

		'forbidden' => 'Não tem permissões para gerenciar utilizadores.',
	),

	'auth'   => array(

        'login_required'    => 'O campo email é requerido.',

        'password_required' => 'O campo palavra-passe é requerido.',

        'wrong_password'    => 'Palavra-passe inválida, tente novamente.',

        'unknown_user'      => 'Utilizador desconhecido.',

        'inactive_user'     => 'Utilizador inativo.',

        'suspended_user'    => 'O utilizador foi suspenso por repetidas tentativas de login sem sucesso.',

        'banned_user'       => 'O utilizador foi banido.',

        'logged_out'	    => 'A sessão foi terminada.',

		'reset-email-sent'  => 'Instruções para redefinir sua palavra-passe foram enviadas para o seu email.',

		'password-changed'  => 'Sua palavra-passe foi alterada com sucesso.',

		'reset-error'       => 'Ocorreu um erro ao redefinir sua palavra-passe. Por favor entre em contacto com o administrador do sistema.',
	),

	'import' => array(

		'required'  => 'Para adicionar :resources por favor utilize o website principal e ative as rotinas de actualização automática.',

		'success'   => 'A importação de :resources foi realizada com sucesso.',

		'fail'      => 'A importação falhou. Verifique a fonte e tente novamente.',

		'undefined' => 'A importação falhou. Não foi encontrado um importador para :resources',
	),

	'email-error'    => 'Ocorreu um erro ao enviar o seu email. Por favor tente novamente mais tarde.',

	'token_mismatch' => 'Sua sessão expirou antes que pudesse salvar as mudanças. Se acredita que isto é um erro, por favor entre em contacto com o administrador do sistema.',

	'reorder' => array(

		'success' => 'Ordenação guardada com sucesso.',
	),

);