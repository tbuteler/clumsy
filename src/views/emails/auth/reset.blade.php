<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<h2>@lang('clumsy::titles.reset-password')</h2>

		<div>@lang('clumsy::emails.content.reset-password', ['url' => route('clumsy.do-reset-password', $token)])</div>
	</body>
</html>