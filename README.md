# Encapsulated user sessions.

This library is a simple object oriented alternative to the $_SESSION superglobal that can be read using the same associative array style code. 

Sessions can be addressed using dot notation, allowing for removing whole categories of session data which is particularly useful to log out a user, for example.



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
if($session->has("user")) {
	if($action === "logout") {
		$session->delete("user");
	}
	else {
		$message = "Welcome back, " . $session->get("user.name.first");
	}
}
else {
	$message = "Please log in";
}
```