<?php

return array(

	'count'          => '{0} No :resources found|[1,Inf]:count :resources found',

	'item_added'     => 'New item added successfully.',
	
	'item_updated'   => 'Item was successfully updated.',

	'item_deleted'   => 'Item deleted.',

	'invalid'        => 'Please correct the errors listed below and try again.',

	'required_by'    => 'The item can\'t be deleted because other resources depend on it. Please remove the related items first or delete their relationship and try again',

	'delete_confirm' => 'Are you sure you want delete this item?',

	'user'   => array(

		'added'     => 'New user added successfully.',
		
		'updated'   => 'User was successfully updated.',

		'deleted'   => 'User deleted.',

		'delete_confirm' => 'Are you sure you want delete this user?',

		'suicide'   => 'You cannot delete your own user.',

		'forbidden' => 'You don\'t have permission to manage users.',
	),

	'auth'   => array(

        'login_required'    => 'Login field is required.',
        
        'password_required' => 'Password field is required.',
        
        'wrong_password'    => 'Wrong password, try again.',
        
        'unknown_user'      => 'User was not found.',
        
        'inactive_user'     => 'User is not activated.',
        
        'logged_out'	    => 'You have logged out.',
	),

	'import' => array(

		'required'  => 'To add :resources please use the parent website and run the automatic update routines afterwards.',

		'success'   => ':resources imported successfully.',

		'undefined' => 'Import failed. No importer defined for :resources.',
	),

	'token_mismatch' => 'Your session expired before submitting changes. If you believe this is an error, please contact the website administrator.',

);