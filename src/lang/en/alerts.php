<?php

return array(

	'items'          => 'item|items',

	'count'          => '{0} No :resources found|{1}:count :resource found|[2,Inf]:count :resources found',

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

        'login_required'    => 'Email field is required.',

        'password_required' => 'Password field is required.',

        'wrong_password'    => 'Wrong password, try again.',

        'unknown_user'      => 'User was not found.',

        'inactive_user'     => 'User is not activated.',

        'suspended_user'    => 'User has been suspended due to repeated failed login attempts.',

        'banned_user'       => 'User is banned.',

        'logged_out'	    => 'You have logged out.',

		'reset-email-sent'  => 'Reset password instructions have been sent to your email.',

		'password-changed'  => 'Your password was successfully changed.',

		'reset-error'       => 'An error occurred while resetting your password. Please contact the website administrator.',
	),

	'import' => array(

		'required'  => 'To add :resources please use the parent website and run the automatic update routines afterwards.',

		'success'   => ':resources imported successfully.',

		'fail'      => 'Import failed. Please check the source and try again.',

		'undefined' => 'Import failed. No importer defined for :resources.',
	),

	'email-error'    => 'An error occurred while sending your email. Please try again later.',

	'token_mismatch' => 'Your session expired before submitting changes. If you believe this is an error, please contact the website administrator.',

	'reorder' => array(

		'success' => 'New order saved successfully.',
	),

);