# Encapsulated user sessions.

This library is a simple object oriented alternative to the $_SESSION superglobal allowing application code to be passed encapsulated `SessionStore` objects, so areas of code can have access to their own Session area without having full read-write access to all session variables.

Sessions are addressed using dot notation, allowing for handling categories of session data. This is particularly useful when dealing with user authentication, for example.

***

<a href="https://circleci.com/gh/PhpGt/Session" target="_blank">
	<img src="https://badge.status.php.gt/session-build.svg" alt="Build status" />
</a>
<a href="https://scrutinizer-ci.com/g/PhpGt/Session" target="_blank">
	<img src="https://badge.status.php.gt/session-quality.svg" alt="Code quality" />
</a>
<a href="https://scrutinizer-ci.com/g/PhpGt/Session" target="_blank">
	<img src="https://badge.status.php.gt/session-coverage.svg" alt="Code coverage" />
</a>
<a href="https://packagist.org/packages/PhpGt/Session" target="_blank">
	<img src="https://badge.status.php.gt/session-version.svg" alt="Current version" />
</a>
<a href="http://www.php.gt/session" target="_blank">
	<img src="https://badge.status.php.gt/session-docs.svg" alt="PHP.Gt/Session documentation" />
</a>

## Example usage: Welcome a user by their first name or log out the user

```php
if($session->has("auth")) {
	if($action === "logout") {
		$session->delete("auth");
	}
	else {
		$message = "Welcome back, " . $session->get("auth.user.name");
	}
}
else {
	$message = "Please log in";
}
```