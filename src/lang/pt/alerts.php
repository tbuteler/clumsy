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

		'added'     => 'Novo usuário adicionado com sucesso.',

		'updated'   => 'O usuário foi actualizado com sucesso.',

		'deleted'   => 'O usuário foi removido.',

		'delete_confirm' => 'Tem certeza que deseja remover este usuário?',

		'suicide'   => 'Não é possível remover o seu próprio usuário.',

		'forbidden' => 'Não tem permissões para gerenciar usuários.',
	),

	'auth'   => array(

        'login_required'    => 'O campo login é requerido.',

        'password_required' => 'O campo password é requerido.',

        'wrong_password'    => 'Password inválida, tente novamente.',

        'unknown_user'      => 'Usuário desconhecido.',

        'inactive_user'     => 'Usuário inativo.',

        'suspended_user'    => 'Usuário foi suspenso por repetidas tentativas de login sem sucesso.',

        'banned_user'       => 'Usuário foi banido.',

        'logged_out'	    => 'A sessão foi terminada.',
	),

	'import' => array(

		'required'  => 'Para adicionar :resources por favor utilize o website principal e ative as rotinas de actualização automática.',

		'success'   => 'A importação de :resources foi realizada com sucesso.',

		'fail'      => 'A importação falhou. Verifique a fonte e tente novamente.',

		'undefined' => 'A importação falhou. Não foi encontrado um importador para :resources',
	),

	'token_mismatch' => 'Sua sessão expirou antes que pudesse salvar as mudanças. Se acredita que isto é um erro, por favor entre em contacto com o administrador do sistema.'

);